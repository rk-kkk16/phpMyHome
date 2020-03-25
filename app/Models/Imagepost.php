<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imagepost extends Model
{
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function category() {
        return $this->hasOne('App\Models\ImpCategory', 'id', 'imp_category_id');
    }

    public function comments() {
        return $this->hasMany('App\Models\ImpComment', 'imagepost_id', 'id');
    }

    public function tags() {
        return $this->hasMany('App\Models\ImpTag', 'imagepost_id', 'id');
    }

    public function evals() {
        return $this->hasMany('App\Models\ImpEval', 'imagepost_id', 'id');
    }
}
