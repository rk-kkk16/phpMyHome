@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">スクラップブック投稿</div>


                <div class="card-body">
                    <div>
                    <p class="crumb" style="float:left"><a href="/scrap/">&lt; スクラップブック</a></p>

                    @if (Auth::user()->id == $post->user_id)
                    <p style="float:right">
                        <a href="/scrap/edit/{{$post->id}}"><button class="btn btn-primary" style="padding:2px 10px"><i class="far fa-edit"></i></button></a>
                        <button class="btn btn-danger" style="padding:2px 10px;margin-left:0.8em" onclick="mwOpen('mw_delete')"><i class="fas fa-trash"></i></button>
                    </p>
                    @endif
                    <br clear="both">
                    </div>

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


                    <div class="card">
                        <div class="card-header"><span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->subject}}</div>
                        <div class="card-body">

                            @if (count($imgs) > 0)
                            <!-- imgs -->
                            <div id="imgs-area" class="row">
                                <div id="carouselExampleIndicators" class="carousel slide col-md-6 col-sm-12" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        @foreach ($imgs as $off => $img_scfile)
                                            <li
                                                data-target="#carouselExampleIndicators"
                                                data-slide-to="{{$off}}"
                                                @if ($off == 0)
                                                    class="active"
                                                @endif
                                            ></li>
                                        @endforeach
                                    </ol>
                                    <div class="carousel-inner">
                                        @foreach ($imgs as $off => $img_scfile)
                                        <div class="carousel-item
                                            @if ($off == 0)
                                                active
                                            @endif
                                        ">
                                            <img src="/storage/scrap/{{$img_scfile->id_range}}/{{$img_scfile->scrap_entry_id}}/{{$img_scfile->id}}.{{$img_scfile->file_type}}" class="d-block w-100" alt="{{$img_scfile->file_name}}">
                                        </div>
                                        @endforeach
                                    </div>
                                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous;</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next;</span>
                                    </a>
                                </div>
                            </div>
                            <br>
                            @endif

                            <!-- body -->
                            <div id="bodyhtml">
                                {{$bodyhtml}}
                            </div>


                            @if (count($files) > 0)
                            <!-- download -->
                            <div>
                                <h4><i class="fas fa-download"></i>ダウンロード</h4>
                                <ul class="list-group">
                                @foreach ($files as $file)
                                    <li class="list-group-item">
                                        <a href="/storage/scrap/{{$file->id_range}}/{{$file->scrap_entry_id}}/{{$file->id}}.{{$file->file_type}}" download="{{$file->file_name}}"><i class="fas fa-file"></i> {{$file->file_name}}</a>
                                    </li>  
                                @endforeach
                                </ul>
                            </div>
                            <br>
                            @endif

                            <p style="text-align:right">
                                <i class="fa fa-list-alt"></i>
                                @if ($post->category->parentCategory)
                                    {{$post->category->parentCategory->category_name}} &gt;
                                @endif
                                {{$post->category->category_name}}
                                <br>
                                <i class="fas fa-calendar-alt"></i> {{date('Y/m/d H:i', strtotime($post->created_at))}}
                            </p>

                            <div>
                                goodpoint!
                            </div>

                            <div>
                                <scrap-comment :num=5 :scrap_entry_id="{{$post->id}}" :myuser_id="{{Auth::user()->id}}" ref="sccmnt"/>
                            </div>
                        </div>
                    </div>

                    <div>
                        <!-- @todo next&before post -->
                        @if ($nextpost)
                            <div style="float:left">
                                <a href="/scrap/{{$nextpost->id}}">
                                <button class="btn btn-link">
                                    &lt; {{mb_substr($nextpost->subject, 0, 10)}}…
                                </button></a>
                            </div>
                        @endif


                        @if ($beforepost)
                            <div style="float:right">
                                <a href="/scrap/{{$beforepost->id}}">
                                <button class="btn btn-link">
                                    {{mb_substr($beforepost->subject, 0, 10)}}… &gt;
                                </button></a>
                            </div>
                        @endif
                        <br clear="both">
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- 削除確認modal -->
<div id="mw_delete" class="modal-overlay">
    <div class="modal-inner">
    <form action="/scrap/delete/{{$post->id}}" method="post">
    {{csrf_field()}}
    <p>この投稿を削除します。よろしいですか？</p>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" type="submit">　O K　</button>
        <button type="button" class="btn btn-secondary" onclick="mwClose('mw_delete')">Cancel</button>
    </p>
    </form>
    </div>
</div>

<!-- コメント削除確認modal -->
<div id="mw_cmntdelete" class="modal-overlay">
    <div class="modal-inner">
    <input type="hidden" id="mw_cmntdelete_id" value="">
    <p>このコメントを削除します。よろしいですか？</p>
    <pre id="mw_cmntdelete_txt"></pre>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" onclick="app.__vue__.$refs.sccmnt.deleteComment($('#mw_cmntdelete_id').val());mwClose('mw_cmntdelete');">　O K　</button>
        <button class="btn btn-secondary" onclick="mwClose('mw_cmntdelete')">Cancel</button>
    </p>
    </div>
</div>
<script>
mw_activate_funcs['mw_cmntdelete'] = function(params) {
    $('#loading').removeClass('hidden');
    let id = params.id;
    let url = '/api/scrap/comment/' + id;
    axios.get(url).then(function(response) {
        $('#loading').addClass('hidden');
        let cmnt = response.data.data;
        $('#mw_cmntdelete_txt').text(cmnt.comment);
        $('#mw_cmntdelete_id').val(cmnt.id);
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        mwClose('mw_cmntdelete');
        alert('エラーが発生しました。');
    });
}

// #bodyhtmlからurl記述を取り出し、api問い合わせしてカード表示変換
window.onload = function() {
    var bodyhtmltext = $('#bodyhtml').text();
    let url_mats = bodyhtmltext.match(/https?:\/\/[^\s]+/g);
    if (url_mats) {
        var urls = {};
        for (var i = 0; i < url_mats.length; i++) {
            let url = url_mats[i];
            urls[url] = url;
        }
        for (var url in urls) {
            axios.post('/api/scrap/urlinfo', {url:url})
            .then(response => {
                let linkinfo = response.data.data;
                var dom_id = 'link_url_' + linkinfo.id;
                var ogp_html = '<div id="' + dom_id + '">' + $('#tmpl-ogp-card').html() + '</div>';
                var bodyhtml = $('#bodyhtml').html();
                bodyhtml = bodyhtml.replace(linkinfo.url, ogp_html);
                $('#bodyhtml').html(bodyhtml);

                $('#' + dom_id + ' .ogp-url').attr('href', linkinfo.url);
                $('#' + dom_id + ' .card-header').text(linkinfo.title);
                if (linkinfo.description) {
                    $('#' + dom_id + ' .card-body .ogp-desc').text(linkinfo.description);
                }
                if (linkinfo.image_url) {
                    $('#' + dom_id + ' .card-body .ogp-img').html('<img src="' + linkinfo.image_url + '" style="max-width:300px;max-height:300px;border:solid 1px #ccc;margin-bottom:1em">');
                }
            });
        }
    }
}

</script>

<!-- ogp情報カードテンプレ -->
<div id="tmpl-ogp-card" style="display:none">
    <a class="ogp-url" href="#" target="_blank">
    <div class="card">
        <div class="card-header"></div>
        <div class="card-body">
            <div class="ogp-img"></div>
            <div class="ogp-desc"></div>
        </div>
    </div>
    </a>
</div>
@endsection
