@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">スクラップブック</div>

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

                    <div>
                        <div style="float:left">{{$posts->appends($params)->links()}}</div>
                        <div style="float:right">
                            <a href="/scrap/categories"><button type="button" class="btn btn-secondary" style="padding:2px 10px"><i class="fa fa-list-alt"></i></button></a>
                            <a href="/scrap/add"><button type="button" class="btn btn-primary" style="padding:2px 10px;margin-left:0.8em">＋</button></a>
                        </div>
                        <br clear="both">
                    </div>
                    <div>投稿数：{{$posts->total()}}
                        <button class="btn btn-link" onclick="$('#srch').slideToggle();">
                            @if ($keyword)
                                キーワード:{{$keyword}}
                            @endif
                            @if ($nowcategory && !$nowcategory->is_primary)
                                カテゴリ：{{$nowcategory->category_name}}
                            @endif
                            (条件変更)
                        </button>
                    </div>

                    <!-- search panel -->
                    <div id="srch" style="display:none;margin-top:1em">
                        <form method="get" action="/scrap">
                        <table class="table"><tbody>
                            <tr>
                                <th>キーワード</th>
                                <td class="form-group">
                                    <input type="text" name="keyword" value="{{$keyword}}" class="form-control">
                                </td>
                            </tr><tr>
                                <th>カテゴリ</th>
                                <td class="form-group">
                                    <select name="category_id" class="form-control">
                                        @foreach ($categorys as $category)
                                            <option value="{{$category->id}}"
                                                @if ($category_id == $category->id)
                                                    selected
                                                @endif
                                            >
                                                {{$category->category_name}}
                                            </option>
                                            @if (count($category->childs) > 0)
                                                @foreach ($category->childs as $child)
                                                    <option
                                                        value="{{$child->id}}"
                                                        @if ($child->id == $category_id)
                                                            selected
                                                        @endif
                                                    >
                                                        {{$category->category_name}} &gt; {{$child->category_name}}
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                            </tr><tr>
                                <td colspan="2" style="text-align:right">
                                    <button type="submit" class="btn btn-primary">検　索</button>
                                </td>
                            </tr>
                        </tbody></table>
                        </form>
                    </div>
                    <!-- /search panel -->

                    <hr>

                    @forelse ($posts as $post)
                        <div class="card">
                            <div class="card-header">
                                <a href="/scrap/{{$post->id}}">
                                    <span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->subject}}
                                </a>
                            </div>
                            <div class="card-body">
                                <a href="/scrap/{{$post->id}}">
                                    @if ($descriptions[$post->id]['type'] == 'image')
                                        <img src="/storage/scrap/{{$descriptions[$post->id]['data']->id_range}}/{{$descriptions[$post->id]['data']->scrap_entry_id}}/{{$descriptions[$post->id]['data']->id}}.{{$descriptions[$post->id]['data']->file_type}}" style="border:solid 1px #ccc; width:100%; max-width:400px">
                                    @elseif ($descriptions[$post->id]['type'] == 'link')
                                        <p class="link-card" id="link-card-{{$post->id}}" data-id="{{$post->id}}">{{$descriptions[$post->id]['data']}}</p>
                                    @else
                                        {{$descriptions[$post->id]['data']}}
                                    @endif
                                </a>

                                <p style="text-align:right">
                                    <i class="fa fa-list-alt"></i>
                                    @if ($post->category->parentCategory)
                                        {{$post->category->parentCategory->category_name}} &gt;
                                    @endif
                                    {{$post->category->category_name}}
                                    <br>
                                    <i class="fas fa-calendar-alt"></i> {{date('Y/m/d H:i', strtotime($post->created_at))}}
                                </p>
                            </div>
                        </div>
                        <br>
                    @empty
                        <p>投稿はありません。</p>
                    @endforelse

                    <hr>
                    <div>{{$posts->appends($params)->links()}}</div>
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
