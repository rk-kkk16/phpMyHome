<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;

class UsersController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // ユーザー一覧画面
    public function index(Request $request) {
        $users = User::paginate(20);
        $page = (int)$request->page ?: 1;
        return view('users', ['users' => $users, 'page' => $page]);
    }

    // ユーザー詳細
    public function detail(Request $request) {
        $id = (int)$request->id ?: 0;
        $user = User::find($id);
        $page = (int)$request->page ?: 1;
        return view('userdetail', ['user' => $user, 'page' => $page]);
    }
}
