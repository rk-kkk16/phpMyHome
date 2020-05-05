<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class GoodPost extends Model
{
    public function points() {
        return $this->hasMany('App\Models\GoodPostPoint', 'good_post_id', 'id');
    }

    public function myPoint() {
        return $this->hasOne('App\Models\GoodPostPoint')
            ->where('user_id', Auth::user()->id);
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function toUser() {
        return $this->hasOne('App\User', 'id', 'to_user_id');
    }
}
