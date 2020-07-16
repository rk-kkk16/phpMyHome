<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ScrapEntry;
use App\Models\ScGoodTrx;
use App\Models\ScLinkInfo;
use App\Http\Resources\ScLinkInfo as ScLinkInfoResource;
use App\Models\ScCategory;
use App\Http\Resources\ScCategory as ScCategoryResource;
use App\Models\ScComment;
use App\Http\Resources\ScComment as ScCommentResource;
use Auth;
use Validator;

class ScrapController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }


    // urlをキーにScLinkInfoモデルを返す
    // 初回問い合わせ時にはurlでWebページから情報取得しogp情報からScLinkInfoを作成、saveする
    public function urlInfo(Request $request) {
        $url = $request->url;
        $md5_url = md5($url);
        $linkinfo = ScLinkInfo::query()->where('md5_url', $md5_url)->first();
        if (!$linkinfo) {
            // @notice サーバーによってfile_get_contents()での外部リソース取得が不可な場合がある
            if ($html = @file_get_contents($url)) {
                $html = mb_convert_encoding($html, 'UTF-8', 'auto');
                $dom = new \DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new \DOMXPath($dom);

                $ogps = [
                    'og:title' => null,
                    'og:image' => null,
                    'og:description' => null,
                ];

                foreach ($ogps as $ogp_name => $ogp_val) {
                    if ($xpath->query("//meta[@property='$ogp_name']")) {
                        $ogps[$ogp_name] = $xpath->query("//meta[@property='$ogp_name']/@content")[0]->textContent;
                    }
                }

                if ($ogps['og:title']) {
                    if ($xpath->query("//title")) {
                        $ogps['og:title'] = $xpath->query("//title")[0]->textContent;
                    }
                }

                // descriptionは100文字までで切り詰め
                if ($ogps['og:description']) {
                    if (mb_strlen($ogps['og:description']) > 100) {
                        $ogps['og:description'] = mb_substr($ogps['og:description'], 0, 99) . '…';
                    }
                }

                $linkinfo = new ScLinkInfo();
                $linkinfo->url = $url;
                $linkinfo->md5_url = $md5_url;
                $linkinfo->title = $ogps['og:title'];
                $linkinfo->description = $ogps['og:description'];
                $linkinfo->image_url = $ogps['og:image'];
                $linkinfo->save();
            }
        }

        if (!$linkinfo) {
            return \App::abort(404);
        }
        return new ScLinkInfoResource($linkinfo);
    }


    // カテゴリ登録
    public function validateAddCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:30',
            'parent_category_id' => 'nullable|integer|exists:sc_categories,id',
        ]);

        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('category_name')) {
                $errors['category_name'] = $validator->errors()->first('category_name');
            }
            if ($validator->errors()->has('parent_category_id')) {
                $errors['parent_category_id'] = $validator->errors()->first('parent_category_id');   
            }
            return [
                'result' => 'error',
                'errors' => $errors,
            ];
        }

        $category = new ScCategory();
        $category->category_name = $request->category_name;
        $category->user_id = Auth::user()->id;
        if ($request->parent_category_id) {
            $category->parent_category_id = $request->parent_category_id;
            $category->depth = 2;
        }
        $category->save();
        return new ScCategoryResource($category);
    }

    // コメントリスト取得
    public function listComment(Request $request) {
        $scrap_entry_id = (int)$request->scrap_entry_id ?: null;
        $page = (int)$request->page ?: 1;
        $num = (int)$request->num ?: 5;
        $query = ScComment::query();
        $query->where('scrap_entry_id', $scrap_entry_id);
        $cmnts = $query->orderBy('id', 'desc')->paginate($num);
        return ScCommentResource::collection($cmnts);
    }

    // 単体コメント取得
    public function comment(Request $request) {
        $id = (int)$request->id ?: null;
        $cmnt = ScComment::findOrfail($id);
        return new ScCommentResource($cmnt);
    }

    // コメントの投稿 validation=>insert
    public function validateAddComment(Request $request) {
        $validator = Validator::make($request->all(), [
            'scrap_entry_id' => 'required|integer|exists:scrap_entries,id',
            'comment' => 'required|string|max:1000',
        ]);
        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('scrap_entry_id')) {
                $errors['scrap_entry_id'] = $validator->errors()->first('scrap_entry_id');
            }
            if ($validator->errors()->has('comment')) {
                $errors['comment'] = $validator->errors()->first('comment');
            }
            return [
                'result' => 'error',
                'errors' => $errors,
            ];
        }
        $cmnt = new ScComment();
        $cmnt->scrap_entry_id = $request->scrap_entry_id;
        $cmnt->comment = $request->comment;
        $cmnt->user_id = Auth::user()->id;
        $cmnt->save();

        return new ScCommentResource($cmnt);
    }

    // コメントの削除
    public function delComment(Request $request) {
        $id = $request->id;
        $cmnt = ScComment::findOrfail($id);
        $cmnt->delete();
        return new ScCommentResource($cmnt);
    }


    // GoodPoint関連
    // 1投稿について現在のscrap_entry.good_point値と今日自分がその投稿に対してgoodを押したかどうかを返す
    public function getGoodStatus(Request $request) {
        $id = $request->id;
        $post = ScrapEntry::findOrfail($id);
        $today_trx = ScGoodTrx::query()
                    ->where('user_id', Auth::user()->id)
                    ->where('date_idx', date('Ymd'))
                    ->where('scrap_entry_id', $id)
                    ->first();
        return [
            'total_point' => $post->good_point,
            'pushed_today' => ($today_trx) ? true : false,
        ];
    }

    // 指定投稿についてgoodをつける
    // 本日分ScGoodTrxレコードの存在確認=>なければレコード生成=>good_point+1
    public function addGoodPoint(Request $request) {
        $id = $request->id;
        $user_id = Auth::user()->id;
        $post = ScrapEntry::findOrfail($id);
        $today_trx = ScGoodTrx::query()
                    ->where('user_id', $user_id)
                    ->where('date_idx', date('Ymd'))
                    ->where('scrap_entry_id', $id)
                    ->first();

        if (!$today_trx) {
            $today_trx = new ScGoodTrx();
            $today_trx->scrap_entry_id = $id;
            $today_trx->user_id = $user_id;
            $today_trx->date_idx = date('Ymd');
            $today_trx->save();

            $post->good_point += 1;
            $post->save();
        }

        return [
            'total_point' => $post->good_point,
            'pushed_today' => true,
        ];
    }
}
