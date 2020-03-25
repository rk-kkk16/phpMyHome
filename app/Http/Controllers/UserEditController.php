<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;

class UserEditController extends Controller
{
    public $user;

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        return view('useredit', []);
    }

    // 名前の変更validate=>保存
    public function validateEdit(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return redirect('/useredit')->withErrors($validator)->with('error', '入力に不備があります。');
        }

        // 変更実施
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return redirect('/useredit')->with('status', '名前を変更しました。');
    }

    // 画像ファイルアップロード
    public function upload(Request $request) {
        $validator = Validator::make($request->all(), [
            'upload_img' => [
                // 必須
                'required',
                // アップロードされたファイルであること
                'file',
                // 画像ファイルであること
                'image',
                // MIMEタイプを指定
                'mimes:jpeg,png',
                // 最小縦横120px 最大縦横1000px
                'dimensions:min_width=120,min_height=120,max_width=1000,max_height=1000',
            ]
        ]);

        if ($validator->fails()) {
            return redirect('/useredit')->withErrors($validator)->with('error', 'ファイルアップロードに失敗しました。');
        }

        // ファイルの保存 user_id.jpg|png
        $filetype = 'jpg';
        if (preg_match('/\.png$/i', $request->file('upload_img')->getClientOriginalName())) {
            $filetype = 'png';
        }
        $savename = Auth::user()->id . '.' . $filetype;
        $request->file('upload_img')->storeAs('public/profile', $savename);

        return redirect('/useredit')->with('status', 'プロフィール画像をアップロードしました。');
    }
}
