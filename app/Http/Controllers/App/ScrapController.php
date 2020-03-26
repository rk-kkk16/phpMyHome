<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use App\Models\ScrapEntry;
use App\Models\ScCategory;
use App\Models\ScFile;
use Storage;
use Validator;

class ScrapController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // index 仮おき
    public function index(Request $request) {
        return view('app.scrap.index', []);
    }


    // edit view
    public function edit(Request $request) {
        // @todo index,detailからのパラメータ引継ぎ

        $post = new ScrapEntry();
        if (isset($request->id)) {
            $post = ScrapEntry::findOrfail($request->id);
        }
        $subject = old('subject', $post->subject);
        $body = old('body', $post->body);
        $sc_category_id = old('sc_category_id', $post->sc_category_id);
        $uploads = old('upload', []);
        $categorys = ScCategory::all();

        return view('app.scrap.edit', [
            'post' => $post,
            'subject' => $subject,
            'body' => $body,
            'sc_category_id' => $sc_category_id,
            'uploads' => $uploads,
            'categorys' => $categorys,
            'max_upload_file' => 5,
        ]);
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
}
