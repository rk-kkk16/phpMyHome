<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mustbuy;
use App\Http\Resources\Mustbuy as MustbuyResource;
use Validator;

use Illuminate\Support\Carbon;
use Auth;
use App\User;

class MustbuyController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function list(Request $request) {
        // requestからパラメータpage,state,sort_key,sort_order,numをとる
        // pageはとらなくてもよかった？
        $page = (int)$request->page ?: 1;
        $state = ($request->state == 'done') ? 'done' : 'yet';
        $sort_key = ($request->sort_key == 'level') ? 'level' : 'created_at';
        $sort_order = ($request->sort_order == 'asc') ? 'asc' : 'desc';
        $num = (int)$request->num ?: 15;

        $query = Mustbuy::query();
        $query->orWhere('state', $state);
        $query->orderBy($sort_key, $sort_order);

        $mustbuys = $query->paginate($num);

        return MustbuyResource::collection($mustbuys);
    }

    // 1件取得
    public function detail(Request $request) {
        $id = (int)$request->id ?: 0;
        $mustbuy = Mustbuy::findOrfail($id);
        return new MustbuyResource($mustbuy);
    }

    // 登録、更新 validate→insert,update
    public function validateRegist(Request $request) {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:100',
            'quantity' => 'required|integer|between:1,999',
            'level' => 'required|integer|between:1,5',
            //'memo' => 'string|max:100',
        ]);

        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('item_name')) {
                $errors['item_name'] = $validator->errors()->first('item_name');
            }
            if ($validator->errors()->has('quantity')) {
                $errors['quantity'] = $validator->errors()->first('quantity');
            }
            if ($validator->errors()->has('level')) {
                $errors['level'] = $validator->errors()->first('level');
            }
            return [
                'result' => 'error',
                'errors' => $errors,
            ];
        }

        $id = (int)$request->id ?: 0;
        $mustbuy = Mustbuy::find($id);
        $user = Auth::user();

        if ($mustbuy) {
            // update
            $mustbuy->item_name = $request->item_name;
            $mustbuy->quantity = $request->quantity;
            $mustbuy->level = $request->level;
            //$mustbuy->memo = $request->memo ?: null;
            $mustbuy->edited_user_id = $user->id;
            $mustbuy->edited_at = Carbon::now();

            if ($mustbuy->save()) {
                return new MustbuyResource($mustbuy);
            }
        } else {
            // insert
            $mustbuy = new Mustbuy();
            $mustbuy->item_name = $request->item_name;
            $mustbuy->quantity = $request->quantity;
            $mustbuy->level = $request->level;
            //$mustbuy->memo = $request->memo ?: null;
            $mustbuy->create_user_id = $user->id;

            if ($mustbuy->save()) {
                return new MustbuyResource($mustbuy);
            }
        }
    }


    // 削除
    public function delete(Request $request) {
        $id = $request->id;
        $mustbuy = Mustbuy::findOrfail($id);
        if ($mustbuy->delete()) {
            return new MustbuyResource($mustbuy);
        }
    }


    // stateの更新 state=='done'ならbuy_atとbuy_user_idを更新する
    public function toggleState(Request $request) {
        $id = $request->id;
        $newstate = $request->newstate;
        $mustbuy = Mustbuy::findOrfail($id);

        $mustbuy->state = $newstate;
        if ($newstate == 'done') {
            $mustbuy->buy_at = Carbon::now();
            $mustbuy->buy_user_id = Auth::user()->id;
        }

        if ($mustbuy->save()) {
            return new MustbuyResource($mustbuy);
        }
    }
}
