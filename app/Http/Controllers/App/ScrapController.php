<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use App\Models\ScrapEntry;
use App\Models\ScCategory;
use App\Models\ScFile;
use App\Models\ScGoodTrx;
use Storage;
use Validator;
use App\Support\Markdown;

class ScrapController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // index 仮おき
    public function index(Request $request) {
        $posts = ScrapEntry::query()->orderBy('id', 'desc')->paginate(10);
        return view('app.scrap.index', [
                    'posts' => $posts,
                ]);
    }

    // 投稿詳細表示
    public function detail(Request $request) {
        $id = $request->id;
        $post = ScrapEntry::findOrfail($id);
        $bodyhtml = Markdown::parse($post->body);

        // 前後の投稿取得
        // @todo 前画面から引き継いだカテゴリなどの条件を適用する
        $query_next = ScrapEntry::query();
        $query_before = ScrapEntry::query();
        $query_next->where('id', '>', $id)->orderBy('id', 'asc');
        $query_before->where('id', '<', $id)->orderBy('id', 'desc');
        $nextpost = $query_next->limit(1)->first();
        $beforepost = $query_before->limit(1)->first();

        // アップロードファイルの仕訳
        $imgs = array();
        $files = array();
        if (count($post->files) > 0) {
            foreach ($post->files as $scfile) {
                if ($scfile->is_image) {
                    $imgs[] = $scfile;
                } else {
                    $files[] = $scfile;
                }
            }
        }

        return view('app.scrap.detail', [
            'post' => $post,
            'bodyhtml' => $bodyhtml,
            'beforepost' => $beforepost,
            'nextpost' => $nextpost,
            'imgs' => $imgs,
            'files' => $files,
        ]);
    }

    // add view
    public function add(Request $request) {
        $post = new ScrapEntry();
        $subject = old('subject', '');
        $body = old('body', '');
        $sc_category_id = old('sc_category_id', '');
        $uploads = old('upload', []);
        $categorys = ScCategory::all();

        $scfile_delchks = old('delfile', []);

        return view('app.scrap.edit', [
            'post' => $post,
            'subject' => $subject,
            'body' => $body,
            'sc_category_id' => $sc_category_id,
            'uploads' => $uploads,
            'categorys' => $categorys,
            'max_upload_file' => 5,
            'scfile_delchks' => $scfile_delchks,
        ]);
     }

    // edit view
    public function edit(Request $request) {
        // @todo index,detailからのパラメータ引継ぎ

        // 自分の投稿でない場合は404
        $post = ScrapEntry::query()->where('id', $request->id)->where('user_id', Auth::user()->id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        $subject = old('subject', $post->subject);
        $body = old('body', $post->body);
        $sc_category_id = old('sc_category_id', $post->sc_category_id);
        $uploads = old('upload', []);
        $categorys = ScCategory::all();

        $scfile_delchks = old('delfile', []);

        return view('app.scrap.edit', [
            'post' => $post,
            'subject' => $subject,
            'body' => $body,
            'sc_category_id' => $sc_category_id,
            'uploads' => $uploads,
            'categorys' => $categorys,
            'max_upload_file' => 5,
            'scfile_delchks' => $scfile_delchks,
        ]);
    }


    // 編集投稿 validate=>save
    public function validateEdit(Request $request) {
        $id = $request->id;
        // 自分の投稿でない場合は404
        $post = ScrapEntry::query()->where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        $validate_opts = [
            'subject' => 'required|string|max:100',
            'body' => 'required|string|max:10000',
            'sc_category_id' => 'required|integer|exists:sc_categories,id',
        ];
        // ファイルアップロードがあった場合
        $has_upload = false;
        if (isset($request->upload) && is_array($request->upload)) {
            $has_upload = true;
            $validate_opts['upload.*'] = [
                'required',
                'string',
                function($attribute, $value, $fail) {
                    // file_path;file_name;is_image(0|1)
                    $ary = explode(';', $value);
                    if (count($ary) !== 3 || ((int)$ary[2] !== 0 && (int)$ary[2] !== 1)) {
                        return $fail('アップロードファイルのデータが不正です。');
                    }
                    $disk = Storage::disk('public');
                    $file_path = preg_replace('|^/storage/|', '', $ary[0]);
                    if (!$disk->exists($file_path)) {
                        return $fail('アップロードファイルが存在しません。');
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $validate_opts);
        if ($validator->fails()) {
            return redirect('/scrap/edit/' . $id)->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // ScFile削除指定のチェック
        $delfiles = $request->delfile ?: [];
        if (count($delfiles) > 0) {
            foreach ($delfiles as $delfile_id) {
                $del_scfile = ScFile::find($delfile_id);
                if (!$del_scfile) {
                    return redirect('/scrap/edit/' . $id)->with('error', '削除ファイルの指定が不正です。')->withInput();
                }
            }
        }

        // 編集の保存
        $post->subject = $request->subject;
        $post->body = $request->body;
        $post->sc_category_id = $request->sc_category_id;
        $post->save();

        // ファイルの移動とScFile登録
        if ($has_upload) {
            $disk = Storage::disk('public');
            foreach ($request->upload as $upload) {
                list($file_path, $file_name, $is_image) = explode(';', $upload);

                $file_path = preg_replace('|^/storage/|', '', $file_path);

                $file_type = preg_replace('/^.*\.([^\.]+)$/', '\1', $file_path);
                if (!$file_name) {
                    $file_name = date('YmdHis') . microtime(true) . '.' . $file_type;
                }
                $file_size = filesize($disk->path($file_path));
                $scfile = new ScFile();
                $scfile->user_id = $post->user_id;
                $scfile->scrap_entry_id = $post->id;

                $id_range_a = (int)($post->id / 100) + 1;
                $id_range_b = $id_range_a + 99;
                $scfile->id_range = $id_range_a . '-' . $id_range_b;

                $scfile->file_type = $file_type;
                $scfile->file_name = $file_name;
                $scfile->file_size = $file_size;
                $scfile->is_image = ($is_image);
                $scfile->save();
                // 保存パス scrap/id-range/entry_id/file_id.file_type
                $save_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
                $disk->move($file_path, $save_path);
            }
        }

        // scfile削除
        if (count($delfiles) > 0) {
            $disk = Storage::disk('public');
            foreach ($delfiles as $delfile_id) {
                $del_scfile = ScFile::find($delfile_id);
                $del_file_path = 'scrap/' . $del_scfile->id_range . '/' . $del_scfile->scrap_entry_id . '/' . $del_scfile->id . '.' . $del_scfile->file_type;
                $del_scfile->delete();
                $disk->delete($del_file_path);
            }
        }

        return redirect('/scrap/' . $post->id)->with('status', '投稿の編集を保存しました。');
    }

    // 新規投稿 validate=>create
    public function validateAdd(Request $request) {
        $validate_opts = [
            'subject' => 'required|string|max:100',
            'body' => 'required|string|max:10000',
            'sc_category_id' => 'required|integer|exists:sc_categories,id',
        ];
        // ファイルアップロードがあった場合
        $has_upload = false;
        if (isset($request->upload) && is_array($request->upload)) {
            $has_upload = true;
            $validate_opts['upload.*'] = [
                'required',
                'string',
                function($attribute, $value, $fail) {
                    // file_path;file_name;is_image(0|1)
                    $ary = explode(';', $value);
                    if (count($ary) !== 3 || ((int)$ary[2] !== 0 && (int)$ary[2] !== 1)) {
                        return $fail('アップロードファイルのデータが不正です。');
                    }
                    $disk = Storage::disk('public');
                    $file_path = preg_replace('|^/storage/|', '', $ary[0]);
                    if (!$disk->exists($file_path)) {
                        return $fail('アップロードファイルが存在しません。');
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $validate_opts);
        if ($validator->fails()) {
            return redirect('/scrap/add')->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // ScrapEntry登録
        $entry = new ScrapEntry();
        $entry->subject = $request->subject;
        $entry->body = $request->body;
        $entry->sc_category_id = $request->sc_category_id;
        $entry->user_id = Auth::user()->id;
        $entry->save();

        // ファイルの移動とScFile登録
        if ($has_upload) {
            $disk = Storage::disk('public');
            foreach ($request->upload as $upload) {
                list($file_path, $file_name, $is_image) = explode(';', $upload);

                $file_path = preg_replace('|^/storage/|', '', $file_path);

                $file_type = preg_replace('/^.*\.([^\.]+)$/', '\1', $file_path);
                if (!$file_name) {
                    $file_name = date('YmdHis') . microtime(true) . '.' . $file_type;
                }
                $file_size = filesize($disk->path($file_path));
                $scfile = new ScFile();
                $scfile->user_id = $entry->user_id;
                $scfile->scrap_entry_id = $entry->id;

                $id_range_a = (int)($entry->id / 100) + 1;
                $id_range_b = $id_range_a + 99;
                $scfile->id_range = $id_range_a . '-' . $id_range_b;

                $scfile->file_type = $file_type;
                $scfile->file_name = $file_name;
                $scfile->file_size = $file_size;
                $scfile->is_image = ($is_image);
                $scfile->save();

                // 保存パス scrap/id-range/entry_id/file_id.file_type
                $save_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
                $disk->move($file_path, $save_path);
            }
        }

        return redirect('/scrap/')->with('status', '投稿を追加しました。');
    }


    // 投稿削除
    public function delete(Request $request) {
        $id = $request->id;
        // 自分の投稿でない場合は404
        $post = ScrapEntry::query()->where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        $scfiles = $post->files;

        $post->delete();

        // 関連するScComment ScFile ScGoodTrxを削除
        $goodtrxs = ScGoodTrx::query()->where('scrap_entry_id', $id)->get();
        foreach ($goodtrxs as $goodtrx) {
            $goodtrx->delete();
        }

        // ScFileの削除 実ファイルも削除する
        $disk = Storage::disk('public');
        foreach ($scfiles as $scfile) {
            $del_file_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
            $scfile->delete();
            $disk->delete($del_file_path);
        }

        return redirect('/scrap/')->with('status', '投稿を削除しました。');
    }
}
