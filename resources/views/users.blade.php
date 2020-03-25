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


                    <div>{{$users->links()}}</div>
                    <div>
                        ユーザー数：{{$users->total()}}人
                    </div>

                    <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th>ID</th><th>ユーザー名</th><th>メールアドレス</th><th>状態</th><th></th>
                    </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{$user->id}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>--</td>
                                <td><a href="/users/{{$user->id}}?page={{$page}}"><button class="btn btn-secondary">詳細</button></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">ユーザー登録がありません。</td></tr>
                        @endforelse
                    </tbody>
                    </table>

                    <div>{{$users->links()}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
