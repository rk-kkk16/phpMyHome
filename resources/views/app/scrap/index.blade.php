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
                        <div style="float:left">{{$posts->links()}}</div>
                        <a href="/scrap/add"><button type="button" class="btn btn-primary" style="float:right">＋</button></a>
                        <br clear="both">
                    </div>
                    <div>投稿数：{{$posts->total()}}
                        <!-- todo: 検索条件変更 -->
                    </div>

                    <!-- todo: searchpanel -->

                    <hr>

                    @forelse ($posts as $post)
                        <div class="card">
                            <div class="card-header">
                                <a href="/scrap/{{$post->id}}">
                                    <span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->subject}}
                                </a>
                            </div>
                            <div class="card-body">
                                <a href="/scrap/{{$post->id}}">本文</a>
                            </div>
                        </div>
                        <br>
                    @empty
                        <p>投稿はありません。</p>
                    @endforelse

                    <hr>
                    <div>{{$posts->links()}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
