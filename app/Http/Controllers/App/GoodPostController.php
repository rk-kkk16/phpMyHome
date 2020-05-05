<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodPost;
use Auth;
use Validator;
use App\User;

class GoodPostController extends Controller
{
    private $heartStr = '💞';

    public function __construct() {
        $this->middleware('auth');
    }


    public function index(Request $request) {
        $posts = GoodPost::query()->orderBy('id', 'desc')->paginate(10);
        $added_id = (int)$request->input('added') ?: null;
        return view('app.goodpost.index', [
                        'posts' => $posts,
                        'added_id' => $added_id,
                        'heartStr' => $this->heartStr,
                    ]);
    }


    // 投稿画面
    public function add(Request $request) {
        $pre_page = (int)$request->input('pre_page') ?: null;
        $users = [];
        // 自分が一番上に来るように
        $users[] = Auth::user();
        $others = User::query()->where('id', '!=', Auth::user()->id)->get();
        foreach ($others as $other) {
            $users[] = $other;
        }
        return view('app.goodpost.add', ['users' => $users, 'pre_page' => $pre_page]);
    }

    // 投稿validate&save
    public function validateAdd(Request $request) {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:100',
            'to_user_id' => 'required|integer|exists:users,id',
        ]);
        if ($validator->fails()) {
            $pre_page = (int)$request->input('pre_page') ?: null;
            return redirect('/goodpost/add?pre_page=' . $pre_page)->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // save
        $post = new GoodPost();
        $post->body = $request->body;
        $post->to_user_id = $request->to_user_id;
        $post->user_id = Auth::user()->id;
        $post->save();
        return redirect('/goodpost/?added=' . $post->id)->with('status', '投稿しました。')->with('added', $post->id);
    }
}
