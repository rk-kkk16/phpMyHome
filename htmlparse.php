<?php

// htmlパースのテスト あとで消すこと
$url = 'https://inside.dmm.com';
$html = file_get_contents($url);
$html = mb_convert_encoding($html, 'UTF-8', 'auto');
$dom = new DOMDocument;
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
//var_dump($dom->getElementsByTagName('meta'));
$title = $xpath->query('//title');
var_dump($title[0]);

$tgts = ['og:title', 'og:image', 'og:description'];
foreach ($tgts as $tgt) {
    if ($xpath->query("//meta[@property='$tgt']")) {
        $content = $xpath->query("//meta[@property='$tgt']/@content");
        var_dump($content[0]->textContent);
    }
}

?>
