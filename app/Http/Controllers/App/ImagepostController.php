<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Imagepost;
use App\Models\ImpCategory;
use App\Models\ImpTag;
use Auth;
use Validator;
use Storage;

use Illuminate\Support\Facades\Config;

class ImagepostController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }


    // 一覧表示
    // @todo 評価値指定での検索 => 別メソッドが適切？
    public function index(Request $request) {
        $num = 10;

        $impeval = $request->input('impeval') ?: null;
        $tag = $request->input('tag') ?: null;
        $category_id = $request->input('category_id') ?: null;
        $nowcategory = null;
        if ($category_id) {
            $nowcategory = ImpCategory::find($category_id);
        }

        if ($impeval || $tag || $category_id) {
            $query = Imagepost::query();
            if ($impeval) {
                $query->whereHas('evals',
                    function($query) use ($impeval) {
                        $query->where('score', $impeval);
                    }
                );
            }

            if ($tag) {
                $query->whereHas('tags',
                    function($query) use ($tag) {
                        $query->where('tag', $tag);
                    }
                );
            }

            if ($category_id) {
                $query->where('imp_category_id', $category_id);
            }

            $imageposts = $query->orderBy('id', 'desc')->paginate($num);
        } else {
            $imageposts = Imagepost::orderBy('id', 'desc')->paginate($num);
        }

        // pagination用のparams
        $params = [];
        if ($impeval) {
            $params['impeval'] = $impeval;
        }
        if ($tag) {
            $params['tag'] = $tag;
        }
        if ($category_id) {
            $params['category_id'] = $category_id;
        }

        return view('app.imagepost.index', [
            'categorys' => ImpCategory::all(),
            'nowcategory' => $nowcategory,
            'imageposts' => $imageposts,
            'category_id' => $category_id,
            'tag' => $tag,
            'impeval' => $impeval,
            'params' => $params,
            'evalscores' => Config::get('app_imagepost.emojis'),
        ]);
    }


    // 詳細画面
    public function detail(Request $request) {
        $pre_page = (int)$request->input('pre_page') ?: null;
        $pre_impeval = $request->input('pre_impeval') ?: null;
        $pre_tag = $request->input('pre_tag') ?: '';
        $pre_category_id = (int)$request->input('pre_category_id') ?: null;

        $id = (int)$request->id ?: 0;
        $imagepost = Imagepost::findOrfail($id);

        // pre_xxxで前後の投稿を探す
        $nextpost = null;
        $beforepost = null;
        $query_next = Imagepost::query();
        $query_before = Imagepost::query();
        if ($pre_impeval) {
            $query_next->whereHas('evals',
                function($query) use ($pre_impeval) {
                    $query->where('score', $pre_impeval);
                }
            );
            $query_before->whereHas('evals',
                function($query) use ($pre_impeval) {
                    $query->where('score', $pre_impeval);
                }
            );
        }
        if ($pre_tag) {
            $query_next->whereHas('tags',
                function($query) use ($pre_tag) {
                    $query->where('tag', $pre_tag);
                }
            );
            $query_before->whereHas('tags',
                function($query) use ($pre_tag) {
                    $query->where('tag', $pre_tag);
                }
            );
        }
        if ($pre_category_id) {
            $query_next->where('imp_category_id', $pre_category_id);
            $query_before->where('imp_category_id', $pre_category_id);
        }
        $query_next->where('id', '>', $id)->orderBy('id', 'asc');
        $query_before->where('id', '<', $id)->orderBy('id', 'desc');
        $nextposts = $query_next->limit(1)->get();
        if (count($nextposts) > 0) {
            $nextpost = $nextposts[0];
        }
        $beforeposts = $query_before->limit(1)->get();
        if (count($beforeposts) > 0) {
            $beforepost = $beforeposts[0];
        }

        return view('app.imagepost.detail', [
            'post' => $imagepost,
            'nextpost' => $nextpost,
            'beforepost' => $beforepost,
            'pre_impeval' => $pre_impeval,
            'pre_page' => $pre_page,
            'pre_tag' => $pre_tag,
            'pre_category_id' => $pre_category_id,
            'emojis' => Config::get('app_imagepost.emojis'),
        ]);
    }


    // 編集画面
    public function edit(Request $request) {
        $pre_page = (int)$request->input('pre_page') ?: null;
        $pre_impeval = $request->input('pre_impeval') ?: null;
        $pre_tag = $request->input('pre_tag') ?: '';
        $pre_category_id = (int)$request->input('pre_category_id') ?: null;

        $id = (int)$request->id ?: 0;
        $imagepost = Imagepost::findOrfail($id);

        $imp_category_id = $request->old('imp_category_id') ?: $imagepost->imp_category_id;

        return view('app.imagepost.edit', [
            'post' => $imagepost,
            'pre_impeval' => $pre_impeval,
            'pre_page' => $pre_page,
            'pre_tag' => $pre_tag,
            'pre_category_id' => $pre_category_id,
            'imp_category_id' => $imp_category_id,
            'categorys' => ImpCategory::all(),
        ]);
    }

    // 編集validate&save
    public function validateEdit(Request $request) {
        $id = (int)$request->id ?: null;
        $imagepost = Imagepost::findOrfail($id);

        $pre_page = (int)$request->input('pre_page') ?: null;
        $pre_impeval = (int)$request->input('pre_impeval') ?: null;
        $pre_tag = $request->input('pre_tag') ?: '';
        $pre_category_id = (int)$request->input('pre_category_id') ?: null;
        $params = [];
        if ($pre_page) {
            $params[] = 'pre_page=' . $pre_page;
        }
        if ($pre_impeval) {
            $params[] = 'pre_impeval=' . $pre_impeval;
        }
        if ($pre_tag) {
            $params[] = 'pre_tag=' . urlencode($pre_tag);
        }
        if ($pre_category_id) {
            $params[] = 'pre_category_id=' . $pre_category_id;
        }
        $paramstr = implode('&', $params);
        if ($paramstr) {
            $paramstr = '?' . $paramstr;
        }

        // 先にrequest->tagtext のスペースを正規化する(全角sp=>半角sp, 連続sp=>>単一sp, 先頭末尾sp削除)
        if($request->tagtext) {
            $tmp = $request->tagtext;
            $tmp = preg_replace('/[\r\t]/', '', $tmp);
            $tmp = preg_replace('/[　]/u', ' ', $tmp);
            $tmp = preg_replace('/[ ]{2,}/', ' ', $tmp);
            $request->tagtext = trim($tmp);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:1000',
            'imp_category_id' => 'nullable|integer',
            'tagtext' => [
                'nullable',
                'string',
                'max:100',
                // 半角記号が含まれていたらNG
                function($attribute, $value, $fail) {
                    if (preg_match('/[\!\"\#\$\%\&\'\(\)\=\-\~\^\|\`\@\{\[\+\;\*\:\]\}\<\>\?]/', $value)) {
                        return $fail(':attributeに半角記号は使えません。');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return redirect('/imagepost/' . $imagepost->id . $paramstr)->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // 登録
        $imagepost->title = $request->title;
        $imagepost->imp_category_id = $request->imp_category_id ?: null;
        $imagepost->tagtext = $request->tagtext ?: null;
        $imagepost->save();

        // tagの作成し直し
        foreach ($imagepost->tags as $oldtag) {
            $oldtag->delete();
        }

        if ($request->tagtext) {
            $tagtext = preg_replace('/[\n]/', ' ', $request->tagtext);
            $tags = explode(' ', $tagtext);
            foreach ($tags as $tag) {
                if (!$tag) {
                    continue;
                }
                $imptag = new ImpTag();
                $imptag->imagepost_id = $imagepost->id;
                $imptag->tag = $tag;
                $imptag->save();
            }
        }

        return redirect('/imagepost/' . $imagepost->id . $paramstr)->with('status', '変更を保存しました。');
    }


    // 投稿画面
    public function add(Request $request) {
        // pre_page pre_tag pre_category_id として前画面(indexなど)のパラメータを引き継ぐ 戻るリンク用
        $pre_page = (int)$request->input('pre_page') ?: null;
        $pre_tag = $request->input('pre_tag') ?: '';
        $pre_category_id = (int)$request->input('pre_category_id') ?: null;
        $pre_impeval = $request->input('pre_impeval') ?: null;

        // upload_img, title, imp_category_id, tagtext
        $upload_img = $request->old('upload_img') ?: '';
        $title = $request->old('title') ?: '';
        $imp_category_id = $request->old('imp_category_id') ?: 0;
        $tagtext = $request->old('tagtext') ?: '';

        return view('app.imagepost.add', [
            'upload_img' => $upload_img,
            'title' => $title,
            'imp_category_id' => $imp_category_id,
            'tagtext' => $tagtext,
            'categorys' => ImpCategory::all(),
            'pre_page' => $pre_page,
            'pre_impeval' => $pre_impeval,
            'pre_tag' => $pre_tag,
            'pre_category_id' => $pre_category_id,
        ]);
    }


    // 投稿validate&save
    public function validateAdd(Request $request) {
        // 先にrequest->tagtext のスペースを正規化する(全角sp=>半角sp, 連続sp=>単一sp, 先頭末尾sp削除)
        if($request->tagtext) {
            $tmp = $request->tagtext;
            $tmp = preg_replace('/[\r\t]/', '', $tmp);
            $tmp = preg_replace('/[　]/u', ' ', $tmp);
            $tmp = preg_replace('/[ ]{2,}/', ' ', $tmp);
            $request->tagtext = trim($tmp);
        }

        $validator = Validator::make($request->all(), [
            'upload_img' => [
                'required',
                'string',
                // 指定の公開フォルダ以下に存在するか
                function($arrtibute, $value, $fail) {
                    $disk = Storage::disk('public');
                    if(!$disk->exists(preg_replace('|^/storage/|', '', $value))) {
                        return $fail(':attributeが存在しません。');
                    }
                }
            ],
            'title' => 'nullable|string|max:1000',
            'imp_category_id' => 'nullable|integer',
            'tagtext' => [
                'nullable',
                'string',
                'max:100',
                // 半角記号が含まれていたらNG
                function($attribute, $value, $fail) {
                    if (preg_match('/[\!\"\#\$\%\&\'\(\)\=\-\~\^\|\`\@\{\[\+\;\*\:\]\}\<\>\?]/', $value)) {
                        return $fail(':attributeに半角記号は使えません。');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return redirect('/imagepost/add')->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // 登録
        $imagepost = new Imagepost();
        $imagepost->title = $request->title;
        $imagepost->user_id = Auth::user()->id;
        $imagepost->imp_category_id = $request->imp_category_id ?: null;
        $imagepost->tagtext = $request->tagtext ?: null;
        $imagepost->save();

        // アップロードファイルの移動
        // public/storage/imageposts/{idrange}/id.(png|jpg|gif)
        $imagepost_id = $imagepost->id;
        $idrange_a = (int)($imagepost_id / 100) + 1;
        $idrange_b = $idrange_a + 99;
        $idrange = $idrange_a . '-' . $idrange_b; // ex:1-100
        $ori_path = preg_replace('|^/storage/|', '', $request->upload_img);
        $filetype = 'jpg';
        if (preg_match('/\.(png|gif)$/', $ori_path, $mat)) {
            $filetype = $mat[1];
        }
        $new_path = 'imageposts/' . $idrange . '/' . $imagepost_id . '.' . $filetype;
        $disk = Storage::disk('public');
        $disk->move($ori_path, $new_path);

        // tagの作成
        if ($request->tagtext) {
            $tagtext = preg_replace('/[\n]/', ' ', $request->tagtext);
            $tags = explode(' ', $tagtext);
            foreach ($tags as $tag) {
                if (!$tag) {
                    continue;
                }
                $imptag = new ImpTag();
                $imptag->imagepost_id = $imagepost_id;
                $imptag->tag = $tag;
                $imptag->save();
            }
        }

        return redirect('/imagepost/')->with('status', '写真をアップロードしました。');
    }


    // imagepost削除 関連imp_evals,imp_tags,imp_comments も削除する
    public function delete(Request $request) {
        $id = (int)$request->id ?: null;

        $pre_tag = $request->input('pre_tag') ?: '';
        $pre_category_id = $request->input('pre_category_id') ?: '';
        $pre_impeval = $request->input('pre_impeval') ?: '';

        $imagepost = Imagepost::findOrfail($id);

        foreach ($imagepost->evals as $ieval) {
            $ieval->delete();
        }
        foreach ($imagepost->tags as $itag) {
            $itag->delete();
        }
        foreach ($imagepost->comments as $icomment) {
            $icomment->delete();
        }

        $imagepost->delete();

        // 画像本体を削除
        $disk = Storage::disk('public');
        $idrange_a = (int)($id / 100) + 1;
        $idrange_b = $idrange_a + 99;
        $idrange = $idrange_a . '-' . $idrange_b;
        $img_path = 'imageposts/' . $idrange . '/' . $id;

        if ($disk->exists($img_path . '.png')) {
            $disk->delete($img_path. '.png');
        } else if($disk->exists($img_path . '.jpg')) {
            $disk->delete($img_path. '.jpg');
        } else if($disk->exists($img_path . '.gif')) {
            $disk->delete($img_path. '.gif');
        }

        $paramstr = 'impeval=' . $pre_impeval . '&tag=' . urlencode($pre_tag) . '&category_id=' . $pre_category_id;

        return redirect('/imagepost/?' . $paramstr)->with('status', '写真を削除しました。');
    }

}
