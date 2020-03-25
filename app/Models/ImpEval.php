<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpEval extends Model
{
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
