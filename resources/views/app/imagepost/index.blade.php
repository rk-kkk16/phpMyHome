@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    写真共有
                </div>

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
                        <div style="float:left">{{$imageposts->appends($params)->links()}}</div>
                        <a href="/imagepost/add?pre_impeval={{$impeval}}&pre_tag={{$tag}}&pre_category_id={{$category_id}}&pre_page={{$imageposts->currentPage()}}"><button type="button" class="btn btn-primary" style="float:right">＋</button></a>
                        <br clear="both">
                    </div>
                    <div>投稿数：{{$imageposts->total()}}
                        　
                        <button class="btn btn-link" onclick="$('#srch').slideToggle();">
                            @if ($tag)
                                タグ:{{$tag}} 
                            @endif
                            @if ($impeval)
                                評価：{{impScoreToEmoji($impeval)}} 
                            @endif
                            @if ($nowcategory)
                                カテゴリ：{{$nowcategory->category_name}} 
                            @endif
                            (条件変更)
                        </button>
                    </div>

                    <!-- search panel -->
                    <div id="srch" style="display:none;margin-top:1em">
                        <form method="get" action="/imagepost">
                        <table class="table"><tbody>
                            <tr>
                                <th>タグ</th>
                                <td class="form-group">
                                    <input type="text" name="tag" value="{{$tag}}" class="form-control">
                                </td>
                            </tr><tr>
                                <th>評価</th>
                                <td class="form-group">
                                    <div style="float:left;margin-left:1em;line-height:45px">
                                        <input type="radio"
                                            id="impeval_0"
                                            name="impeval"
                                            value=""
                                            @if (!$impeval)
                                                checked
                                            @endif
                                        >
                                        <label for="impeval_0">(選択なし)</label>
                                    </div>
                                    @foreach ($evalscores as $escore => $emoji)
                                        <div style="float:left;margin-left:1em;font-size:25px">
                                            <input type="radio"
                                                id="impeval_{{$escore}}"
                                                name="impeval"
                                                value="{{$escore}}"
                                                @if ($impeval == $escore)
                                                    checked
                                                @endif
                                            >
                                            <label for="impeval_{{$escore}}">{{$emoji}}</label>
                                        </div>
                                    @endforeach
                                </td>
                            </tr><!-- <tr>
                                <th>カテゴリ</th>
                                <td class="form-group">
                                    <select name="category_id" class="form-control">
                                        <option value="">(指定なし)</option>
                                        @foreach ($categorys as $category)
                                            <option value="{{$category->id}}"
                                                @if ($category_id == $category->id)
                                                    selected
                                                @endif
                                            >
                                                {{$category->category_name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr> --><tr>
                                <td colspan="2" style="text-align:right">
                                    <button type="submit" class="btn btn-primary">検　索</button>
                                </td>
                            </tr>
                        </tbody></table>
                        </form>
                    </div>
                    <!-- /search panel -->

                    <hr>

                    @forelse ($imageposts as $post)
                        <div class="card">
                            <div class="card-header">
                                <span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->user->name}}
                            </div>
                            <div class="card-body">
                                <a href="/imagepost/{{$post->id}}?pre_page={{$imageposts->currentPage()}}&pre_impeval={{$impeval}}&pre_tag={{$tag}}&pre_category_id={{$category_id}}">
                                <div>
                                    <img src="/imagepost/photo/{{$post->id}}?img_size=400&base_w=400" style="border:solid 1px #ccc; width:100%; max-width:400px">
                                </div>
                                </a>
                                <br>
                                <p>{!!nl2br(url2Link($post->title))!!}</p>
                                <p style="text-align:right"><i class="fas fa-calendar-alt"></i> {{date('Y/m/d H:i', strtotime($post->created_at))}}</p>

                                @if (count($post->evals))
                                <p>
                                    @foreach ($post->evals as $ieval)
                                        <span style="font-size:25px">{{impScoreToEmoji($ieval->score)}}</span>
                                    @endforeach
                                </p>
                                @endif

                                @if (count($post->tags))
                                <p>タグ：
                                    @foreach ($post->tags as $tag4link)
                                        <a href="/imagepost/?tag={{$tag4link->tag}}" style="margin-right:1em">#{{$tag4link->tag}}</a>
                                    @endforeach
                                </p>
                                @endif
                            </div>
                        </div>
                        <br>
                    @empty
                        <p>投稿はありません。</p>
                    @endforelse

                    <hr>
                    <div>{{$imageposts->appends($params)->links()}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
