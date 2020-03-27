<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use Validator;
use App\Models\ScFile;
use Storage;

class ScrapUploadController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // 画像のアップロードを受け付けて仮パスを返す
    public function upload(Request $request) {
        // 画像か否かでvalidatorの設定を切り替える
        $validator_opts = [
            'required',
            'file',
            'max:5000'
        ];

        $is_image = false;
        if (preg_match('/\.(jpe?g|png|gif|bmp)$/i', $request->file('upload')->getClientOriginalName())) {
            $is_image = true;
            $validator_opts[] = 'mimes:jpeg,png,gif,bmp';
            $validator_opts[] = 'dimensions:max_width=5000,max_height=5000';
        }

        $validator = Validator::make($request->all(), [
            'upload' => $validator_opts
        ]);

        if ($validator->fails()) {
            return [
                'result' => 'error',
                'errors' => $validator->errors()->first('upload'),
            ];
        }

        // jpg=>jpegに統一する(content-type流用のため)
        $file_type = null;
        if (preg_match('/\.([a-z0-9]+)$/i', $request->file('upload')->getClientOriginalName(), $mat)) {
            $file_type = strtolower($mat[1]);
            if ($file_type == 'jpg') {
                $file_type = 'jpeg';
            }
        }

        // 元ファイル名
        // iphoneでの撮影からアップロードの場合ファイル名が渡らないので注意
        $original_file_name = $request->file('upload')->getClientOriginalName();

        // 仮ファイル名
        $savename = md5('scfile' . Auth::user()->id . time() . microtime(true)) . '.' . $file_type;
        $request->file('upload')->storeAs('public/tmp', $savename);
        $return_path = '/storage/tmp/' . $savename;

        return [
            'result' => 'ok',
            'uploaded_file' => $return_path,
            'is_image' => ($is_image) ? 1 : 0,
            'original_file_name' => $original_file_name,
        ];
    }


    // 仮パスの画像を削除
    public function deleteTmpFile(Request $request) {
        $uploaded_file = $request->uploaded_file ?: null;
        if (!$uploaded_file) {
            return [
                'result' => 'error',
                'errors' => '不正なパラメータです。',
            ];
        }

        $disk = Storage::disk('public');
        if (!$disk->exists(preg_replace('|^/storage/|', '', $uploaded_file))) {
            return [
                'result' => 'error',
                'errors' => 'ファイルが存在しません。',
            ];
        }

        $disk->delete(preg_replace('|^/storage/|', '', $uploaded_file));
        return [
            'result' => 'ok',
        ];
    }


    // アップロード済(ScFile存在)ファイルを削除
    public function deleteScFile() {
        $id = (int)$request->id ?: null;
        if (!$id) {
            return [
                'result' => 'error',
                'errors' => '不正なパラメータです。',
            ];
        }

        $scfile = ScFile::findOrfail($id);
        if ($scfile->user_id != Auth::user()->id) {
            return [
                'result' => 'error',
                'errors' => '権限がありません。',
            ];
        }

        $scfile->delete();

        $file_path = 'scrap/' . $scfile->id_range . '/' . $scfile->id . '.' . $scfile->file_type;
        $disk = Storage::disk('public');
        $disk->delete($file_path);
        return [
            'result' => 'ok',
        ];
    }
}
