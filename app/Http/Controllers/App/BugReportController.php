<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BugReportController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }


    public function index(Request $request) {
        $state = ($request->state == 'done') ? 'done' : 'yet';
        $sort_key = ($request->sort_key == 'created_at') ? 'created_at' : 'level';
        $sort_order = ($request->sort_order == 'asc') ? 'asc' : 'desc';

        $actives = [
            'yet' => ($state == 'yet') ? 'active' : '',
            'done' => ($state == 'done') ? 'active' : '',
            'level' => ($sort_key == 'level') ? 'active' : '',
            'created_at' => ($sort_key == 'created_at') ? 'active' : '',
            'asc' => ($sort_order == 'asc') ? 'active' : '',
            'desc' => ($sort_order == 'desc') ? 'active' : '',
        ];

        return view('app.bugreport.index', ['state' => $state, 'sort_key' => $sort_key, 'sort_order' => $sort_order, 'actives' => $actives]);
    }

}
