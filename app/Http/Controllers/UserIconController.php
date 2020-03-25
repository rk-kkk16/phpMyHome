<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class UserIconController extends Controller
{
    public function showIcon($id) {
        $disk = Storage::disk('public');
        $mtype = 'png';
        $path = 'profile/default.png';
        if ($disk->exists('profile/' . (int)$id . '.jpg')) {
            $path = 'profile/' . (int)$id . '.jpg';
            $mtype = 'jpeg';
        }
        else if ($disk->exists('profile/' . (int)$id . '.png')) {
            $path = 'profile/' . (int)$id . '.png';
        }
        $realpath = $disk->path($path);
        $header = [
            'Content-Type' => 'image/' . $mtype,
        ];
        return response()->file($realpath, $header);
    }
}
