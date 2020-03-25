<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BugReport;
use App\Http\Resources\BugReport as BugReportResource;
use Validator;

use Illuminate\Support\Carbon;
use Auth;
use App\User;

class BugReportController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }


    public function list(Request $request) {
        // requestからパラメータstate,sort_key,sort_order,numをとる
        // pageはとらなくても処理される
        $state = ($request->state == 'done') ? 'done' : 'yet';
        $sort_key = ($request->sort_key == 'level') ? 'level' : 'created_at';
        $sort_order = ($request->sort_order == 'asc') ? 'asc' : 'desc';
        $num = (int)$request->num ?: 15;

        $query = BugReport::query();
        $query->where('state', $state);
        $bugreports = $query->orderBy($sort_key, $sort_order)->paginate($num);
        return BugReportResource::collection($bugreports);
    }


    public function detail(Request $request) {
        $id = (int)$request->id ?: 0;
        $bugreport = BugReport::findOrfail($id);
        return new BugReportResource($bugreport);
    }


    public function validateRegist(Request $request) {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:100',
            'level' => 'required|integer|between:1,5',
            'description' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('subject')) {
                $errors['subject'] = $validator->errors()->first('subject');
            }
            if ($validator->errors()->has('description')) {
                $errors['description'] = $validator->errors()->first('description');
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
        $bugreport = BugReport::find($id);

        if ($bugreport) {
            // update
            $bugreport->subject = $request->subject;
            $bugreport->level = $request->level;
            $bugreport->description = $request->description;
            if ($bugreport->save()) {
                return new BugReportResource($bugreport);
            }
        } else {
            // insert
            $bugreport = new BugReport();
            $bugreport->subject = $request->subject;
            $bugreport->level = $request->level;
            $bugreport->description = $request->description;
            $bugreport->create_user_id = Auth::user()->id;
            if ($bugreport->save()) {
                return new BugReportResource($bugreport);
            }
        }
    }


    public function delete(Request $request) {
        $id = (int)$request->id;
        $bugreport = BugReport::findOrfail($id);
        if ($bugreport->delete()) {
            return new BugReportResource($bugreport);
        }
    }


    public function toggleState(Request $request) {
        $id = (int)$request->id;
        $newstate = $request->newstate;
        $bugreport = BugReport::findOrfail($id);

        $bugreport->state = $newstate;
        if ($newstate == 'done') {
            $bugreport->done_user_id = Auth::user()->id;
            $bugreport->done_at = Carbon::now();
        }
        if ($bugreport->save()) {
            return new BugReportResource($bugreport);
        }
    }
}
