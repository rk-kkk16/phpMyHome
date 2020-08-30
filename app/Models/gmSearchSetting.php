<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class gmSearchSetting extends Model
{
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function hits() {
        return $this->hasMany('App\Models\gmSearchGameHit', 'gm_search_setting_id', 'id');
    }
}
