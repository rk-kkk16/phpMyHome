<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Storage;
use Image;

/**
 * 指定のimagepost投稿写真を表示する
 * img_w,img_hの指定サイズにリサイズした後base_w,base_hサイズの土台に中央寄せで乗せて表示
 * @param id 対象画像のid指定
 * @param base_w 表示する全体の横幅
 * @param base_h 表示する全体の縦幅
 * @param img_size 画像のリサイズ幅 縦横長い方に対して適用される
**/

class ImagepostImageController extends Controller
{
    public function viewImage(Request $request) {
        if (!$request->id) {
            return \App::abort(404);
        }

        $id = (int)$request->id;
        $base_w = $request->input('base_w') ?: null;
        $base_h = $request->input('base_h') ?: null;
        $img_size = $request->input('img_size') ?: null;

        // base_w,h どちらか欠けてる場合は同じサイズにする
        if ($base_w && !$base_h) {
            $base_h = $base_w;
        } else if(!$base_w && $base_h) {
            $base_w = $base_h;
        }

        $idrange_a = (int)($id / 100) + 1;
        $idrange_b = $idrange_a + 99;
        $idrange = $idrange_a . '-' . $idrange_b;
        $img_path = 'imageposts/' . $idrange . '/' . $id;
        // png,gif,jpgを探す
        $disk = Storage::disk('public');
        if ($disk->exists($img_path . '.png')) {
            $img_path .= '.png';
        } else if($disk->exists($img_path . '.jpg')) {
            $img_path .= '.jpg';
        } else if($disk->exists($img_path . '.gif')) {
            $img_path .= '.gif';
        } else {
            return \App::abort(404);
        }

        // 元画像を指定サイズにリサイズする
        // 縦横比維持のため元画像の長い方についてだけ新サイズ指定
        $img = Image::make($disk->path($img_path));
        // 横長
        if ($img->width() >= $img->height()) {
            if ($img_size) {
                $img->resize($img_size, null, function($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }
        // 縦長
        else {
            if ($img_size) {
                $img->resize(null, $img_size, function($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }

        // base指定ない場合はそのまま出力
        if (!$base_w && !$base_h) {
            return $img->encode('png')->response();
        }

        // baseサイズの土台を作ってリサイズした元画像を載せる
        $base = Image::canvas($base_w, $base_h);
        $base->insert($img, 'center');

        return $base->encode('png')->response();
    }
}
