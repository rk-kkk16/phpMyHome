<?php

/**
 * 共通関数
 * composer.json autoloadセクション>filesに登録
 * composer dump-autoload を実行して組み込む
**/

/**
 * ユーザーごとのアップロードされているプロフィール画像を解決しパスを返す
**/
function profileImage($user_id, $size = 'normal') {
    $disk = Storage::disk('public');
    $path = '/storage/profile/default.png';
    if ($disk->exists('profile/' . (int)$user_id . '.jpg')) {
        $path = '/storage/profile/' . (int)$user_id . '.jpg?' . time();
    }
    else if ($disk->exists('profile/' . (int)$user_id . '.png')) {
        $path = '/storage/profile/' . (int)$user_id . '.png?' . time();
    }

    if ($size == 'small') {
        echo '<span class="profimg prfmini" style="background-image:url('.$path.');"></span>';
    } else {
        echo '<span class="profimg" style="background-image:url('.$path.');"></span>';
    }
}


/**
 * 写真共有 評価スコアの絵文字変換
**/
function impScoreToEmoji($score) {
    $emojis = Config::get('app_imagepost.emojis');
    return $emojis[$score];
}

/**
 * 写真共有 URLをリンクに変換する
**/
function url2Link($text) {
    return preg_replace('/(https?:\/\/[^\s]+)/', '<a href="\1" target="_blank">\1</a>', $text);
}


