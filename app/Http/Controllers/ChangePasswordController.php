<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\User;
use Validator;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{

    private $user;

    public function __construct() {
        $this->middleware('auth');
        $this->user = Auth::user();
    }

    // パスワード変更画面
    public function index(Request $request) {
        return view('changepassword', ['user' => $this->user]);
    }

    // パスワード変更時のバリデーションと変更実行呼び出し
    public function validateChange(Request $request) {
        $validator = Validator::make($request->all(), [
            'password_now' => ['required', 'string', 'max:32',
                    function($attribute, $value, $fail) {
                        if (!Hash::check($value, Auth::user()->password)) {
                            $fail(':attributeが違います。');
                        }
                    },
                ], // 必須・文字列・32文字まで・現パスワードと一致
            'password_new' => 'required|string|max:32', // 必須・文字列・32文字まで
            'password_conf' => 'required|same:password_new|string|max:32',// 必須・文字列・32文字まで・password_newと一致
        ]);

        if ($validator->fails()) {
            return redirect('/changepassword')->withErrors($validator)->with('error','入力に不備があります。');
        }

        // パスワードを更新
        $user = Auth::user();
        $user->password = Hash::make($request->password_new);
        $user->save();
        return redirect('/changepassword')->with('status', 'パスワードを変更しました。');
    }
}
