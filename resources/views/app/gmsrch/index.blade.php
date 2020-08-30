@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">ゲーム探索</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p>ダッシュボード(仮おき)</p>
                    <p>新着情報：最近見つかったゲーム、調査実行履歴、おすすめキーワード</p>
                    <p>リンク：検索条件一覧、見つかったゲーム一覧、おすすめキーワード一覧</p>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// #bodyhtmlからurl記述を取り出し、api問い合わせしてカード表示変換
window.onload = function() {
    $('.link-card').each(function() {
        let post_id = $(this).attr('data-id');
        let url = $(this).text();
        axios.post('/api/scrap/urlinfo', {url:url})
        .then(response => {
            let linkinfo = response.data.data;
            var dom_id = 'link_url_' + linkinfo.id;
            var ogp_html = '<div id="' + dom_id + '">' + $('#tmpl-ogp-card').html() + '</div>';
            $('#link-card-' + post_id).html(ogp_html);

            $('#' + dom_id + ' .card-header').text(linkinfo.title);
            if (linkinfo.description) {
                $('#' + dom_id + ' .card-body .ogp-desc').text(linkinfo.description);
            }
            if (linkinfo.image_url) {
                $('#' + dom_id + ' .card-body .ogp-img').html('<img src="' + linkinfo.image_url + '" style="width:100%;max-width:300px;max-height:300px;border:solid 1px #ccc;margin-bottom:1em">');
            }
        });
    });
}
</script>
<!-- ogp情報カードテンプレ -->
<div id="tmpl-ogp-card" style="display:none">
    <div class="card">
        <div class="card-header"></div>
        <div class="card-body">
            <div class="ogp-img"></div>
            <div class="ogp-desc"></div>
        </div>
    </div>
</div>
@endsection
