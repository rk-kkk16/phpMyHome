@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">ユーザー管理</div>

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


                    <div><a href="/users/?page={{$page}}"><button class="btn">&lt;ユーザー一覧</button></a></div>

                    @if ($user)
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>ID</th><td>{{$user->id}}</td>
                        </tr><tr>
                            <th>ユーザー名</th><td>{{$user->name}} <img src="{{profileImage($user->id)}}" width="80"></td>
                        </tr><tr>
                            <th>登録日</th><td>{{$user->created_at}}</td>
                        </tr><tr>
                            <th>最終更新</th><td>{{$user->updated_at}}</td>
                        </tr>
                    </tbody>
                    </table>
                    @else
                    <p>存在しないユーザーです。</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
