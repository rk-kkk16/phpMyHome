<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class gmSearchGameHit extends Model
{
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function gameData() {
        return $this->hasOne('App\Models\gmSearchGameData', 'id', 'gm_search_game_data_id');
    }
    public function searchSetting() {
        return $this->hasOne('App\Models\gmSearchSetting', 'id', 'gm_search_setting_id');
    }
}
