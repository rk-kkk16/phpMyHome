<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ScLinkInfo;
use App\Http\Resources\ScLinkInfo as ScLinkInfoResource;
use Auth;

class ScrapController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }


    // urlをキーにScLinkInfoモデルを返す
    // 初回問い合わせ時にはurlでWebページから情報取得しogp情報からScLinkInfoを作成、saveする
    public function urlInfo(Request $request) {
        $url = $request->url;
        $md5_url = md5($url);
        $linkinfo = ScLinkInfo::query()->where('md5_url', $md5_url)->first();
        if (!$linkinfo) {
            // @notice サーバーによってfile_get_contents()での外部リソース取得が不可な場合がある
            if ($html = @file_get_contents($url)) {
                $html = mb_convert_encoding($html, 'UTF-8', 'auto');
                $dom = new \DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new \DOMXPath($dom);

                $ogps = [
                    'og:title' => null,
                    'og:image' => null,
                    'og:description' => null,
                ];

                foreach ($ogps as $ogp_name => $ogp_val) {
                    if ($xpath->query("//meta[@property='$ogp_name']")) {
                        $ogps[$ogp_name] = $xpath->query("//meta[@property='$ogp_name']/@content")[0]->textContent;
                    }
                }

                if ($ogps['og:title']) {
                    if ($xpath->query("//title")) {
                        $ogps['og:title'] = $xpath->query("//title")[0]->textContent;
                    }
                }

                // descriptionは100文字までで切り詰め
                if ($ogps['og:description']) {
                    if (mb_strlen($ogps['og:description']) > 100) {
                        $ogps['og:description'] = mb_substr($ogps['og:description'], 0, 99) . '…';
                    }
                }

                $linkinfo = new ScLinkInfo();
                $linkinfo->url = $url;
                $linkinfo->md5_url = $md5_url;
                $linkinfo->title = $ogps['og:title'];
                $linkinfo->description = $ogps['og:description'];
                $linkinfo->image_url = $ogps['og:image'];
                $linkinfo->save();
            }
        }

        if (!$linkinfo) {
            return \App::abort(404);
        }
        return new ScLinkInfoResource($linkinfo);
    }
}
