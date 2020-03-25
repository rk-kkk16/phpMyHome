# phpMyHome

> Social Networking System (SNS) for family use.

## Description/本プログラムについて
PC/スマートフォンブラウザ向けの家庭用SNSです。
現時点(2020/03/25)で「買い物リスト」「写真共有」の機能が存在します。
現在も開発中、機能追加や変更修正を継続して行っています。
使用言語:
- PHP(Laravel 6)
- Vue.js
- MySQL(5以降)

## Disclaimer/免責事項
1. 本アプリを使用したことによる一切の損害について、開発者は責任を負いません。
2. 本アプリについてあらゆる利用、ソース改変、再配布を許可します。

## Build Setup/インストール手順

``` bash
# 必要なモジュールのインストール
$ npm run install
(or $ npm install )
※npmを使用できないサーバー(レンタルサーバーなど)の場合は開発用PC上などで上記コマンドを実行し、node_modules/ 以下をアップロードしてください。
またその場合は
$ npm run watch
を実行してからpublic/css public/js もアップロードし直してください。

$ composer install

$ php artisan migrate

$ php artisan db:seed --class=AdminUser
初期ユーザーパスワードが表示されますのでこれを用いてログインしてください。

$ php artisan storage:link

```

