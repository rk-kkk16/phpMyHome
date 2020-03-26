<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;


class ScrapEntry extends Model
{
    public function category() {
        return $this->hasOne('App\Models\ScCategory', 'id', 'sc_category_id');
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function files() {
        return $this->hasMany('App\Models\ScFile', 'scrap_entry_id', 'id');
    }

    // 閲覧中ユーザーの今日のsc_good_trxsレコードと紐付ける
    public function good_trx() {
        return $this->hasOne('App\Models\ScGoodTrx', 'scrap_entry_id', 'id')
            ->where('user_id', Auth::user()->id)
            ->where('date_idx', date('Ymd'));
    }
}
