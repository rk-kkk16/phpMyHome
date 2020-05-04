<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Models\GoodPost;
use App\Models\GoodPostPoint;

class GoodPostController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function addPoint(Request $request) {
        $id = $request->id;
        $point = (int)$request->point;
        $post = GoodPost::findOrfail($id);
        // 既に自分が対象postへポイントつけ済なら終了
        if ($post->myPoint) {
            return [
                'result' => 'already'
            ];
        }

        // pointがゼロなら保存しない
        if ($point == 0) {
            return [
                'result' => 'failure'
            ];
        }

        $goodpoint = new GoodPostPoint();
        $goodpoint->good_post_id = $id;
        $goodpoint->user_id = Auth::user()->id;
        $goodpoint->point = $point;
        $goodpoint->save();

        // 親postのtotal_goodを更新
        $post->total_good += $goodpoint->point;
        $post->save();

        return [
            'result' => 'success',
            'new_point' => $post->total_good,
        ];
    }
}
