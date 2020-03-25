<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugReport extends Model
{
    public function create_user() {
        return $this->hasOne('App\User', 'id', 'create_user_id');
    }

    public function done_user() {
        return $this->hasOne('App\User', 'id', 'done_user_id');
    }
}
