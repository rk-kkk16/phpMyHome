@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">パスワード変更</div>

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

                    <form action="/changepassword" method="post">
                    {{csrf_field()}}
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>現在のパスワード</th>
                        <td class="form-group">
                            <input type="password" name="password_now" value="" class="form-control">
                            @if ($errors->has('password_now'))
                                <p class="bg-danger">{{$errors->first('password_now')}}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>新しいパスワード</th>
                        <td class="form-group">
                            <input type="password" name="password_new" value="" class="form-control">
                            @if ($errors->has('password_new'))
                                <p class="bg-danger">{{$errors->first('password_new')}}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>新しいパスワード(確認)</th>
                        <td class="form-group">
                            <input type="password" name="password_conf" value="" class="form-control">
                            @if ($errors->has('password_conf'))
                                <p class="bg-danger">{{$errors->first('password_conf')}}</p>                 
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    </table>

                    <div class="text-center"><button type="submit" class="btn btn-primary">変更</button></div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
