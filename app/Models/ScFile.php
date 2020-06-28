<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScFile extends Model
{
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function scrap_entry() {
        return $this->hasOne('App\Models\ScrapEntry', 'id', 'scrap_entry_id');
    }
}
