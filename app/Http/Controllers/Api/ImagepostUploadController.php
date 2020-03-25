<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use Validator;

class ImagepostUploadController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // 画像のアップロードを受け付けて仮パスを返す
    public function upload(Request $request) {
        $validator = Validator::make($request->all(), [
            'upload_img' => [
                'required', // 必須
                'file',
                'image',
                'max:5000',
                'mimes:jpeg,png,gif',
                'dimensions:min_width=120,min_height=120,max_width=5000,max_height=5000',
            ]
        ]);

        if ($validator->fails()) {
            return [
                'result' => 'error',
                'errors' => $validator->errors()->first('upload_img'),
            ];
        }

        $filetype = 'jpg';
        if (preg_match('/\.(png|gif)$/i', $request->file('upload_img')->getClientOriginalName(), $mat)) {
            $filetype = strtolower($mat[1]);
        }
        $savename = md5('imagepost' . Auth::user()->id . time() . microtime(true)) . '.' . $filetype;
        $request->file('upload_img')->storeAs('public/tmp', $savename);
        $return_path = '/storage/tmp/' . $savename;

        return [
            'result' => 'ok',
            'uploaded_file' => $return_path,
        ];
    }

}
