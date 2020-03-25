<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Imagepost;
use App\Models\ImpEval;
use App\Models\ImpComment;
use App\Http\Resources\Imagepost as ImagepostResource;
use App\Http\Resources\ImpEval as ImpEvalResource;
use App\Http\Resources\ImpComment as ImpCommentResource;
use Validator;

use Illuminate\Support\Carbon;
use Auth;
use App\User;

class ImagepostController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // 投稿のリスト取得
    public function list(Request $request) {
        $num = (int)$request->num ?: 5;
        $imageposts = Imagepost::query()->orderBy('id', 'desc')->paginate($num);
        return ImagepostResource::collection($imageposts);
    }


    // コメントのリスト取得
    public function listComment(Request $request) {
        $imagepost_id = (int)$request->imagepost_id ?: null;
        $page = (int)$request->page ?: 1;
        $num = (int)$request->num ?: 5;
        $query = ImpComment::query();
        $query->where('imagepost_id', $imagepost_id);
        $impcmnts = $query->orderBy('id', 'desc')->paginate($num);
        return ImpCommentResource::collection($impcmnts);
    }

    // 単体コメント取得
    public function comment(Request $request) {
        $id = (int)$request->id ?: null;
        $impcmnt = ImpComment::findOrfail($id);
        return new ImpCommentResource($impcmnt);
    }

    // コメントの投稿 validation=>insert
    public function validateAddComment(Request $request) {
        $validator = Validator::make($request->all(), [
            'imagepost_id' => 'required|integer|exists:imageposts,id',
            'comment' => 'required|string|max:1000',
        ]);
        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('imagepost_id')) {
                $errors['imagepost_id'] = $validator->errors()->first('imagepost_id');
            }
            if ($validator->errors()->has('comment')) {
                $errors['comment'] = $validator->errors()->first('comment');
            }
            return [
                'result' => 'error',
                'errors' => $errors,
            ];
        }

        $impcmnt = new ImpComment();
        $impcmnt->imagepost_id = $request->imagepost_id;
        $impcmnt->comment = $request->comment;
        $impcmnt->user_id = Auth::user()->id;
        $impcmnt->save();

        return new ImpCommentResource($impcmnt);
    }

    // コメントの削除
    public function delComment(Request $request) {
        $id = $request->id;
        $impcmnt = ImpComment::findOrfail($id);
        $impcmnt->delete();
        return new ImpCommentResource($impcmnt);
    }


    // 評価の投稿 validation=>insert
    public function validateAddEval(Request $request) {
        $validator = Validator::make($request->all(), [
            'imagepost_id' => 'required|integer|exists:imageposts,id',
            'score' => 'required|integer|between:1,5',
        ]);
        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('imagepost_id')) {
                $errors['imagepost_id'] = $validator->errors()->first('imagepost_id');
            }
            if ($validator->errors()->has('score')) {
                $errors['score'] = $validator->errors()->first('score');
            }
            return [
                'result' => 'error',
                'errors' => $errors,
            ];
        }

        $impeval = new ImpEval();
        $impeval->imagepost_id = $request->imagepost_id;
        $impeval->score = $request->score;
        $impeval->user_id = Auth::user()->id;
        $impeval->save();

        return new ImpEvalResource($impeval);
    }


    // 評価の削除
    public function delEval(Request $request) {
        $id = $request->id;
        $impeval = ImpEval::findOrfail($id);
        $impeval->delete();
        return new ImpEvalResource($impeval);
    }


}
