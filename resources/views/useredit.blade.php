@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">ユーザー設定</div>

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


                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>プロフィール画像</th>
                        <td>
                            <div id="frm_prof_img">
                                {{ profileImage(Auth::user()->id) }}
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="$('#frm_prof_upload_inp').click();">アップロード</button>
                                @if ($errors->has('upload_img'))
                                <p class="bg-danger">{{$errors->first('upload_img')}}</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    </table>
                    <form action="/useredit/upload" id="frm_prof_upload" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <input type="file" name="upload_img" id="frm_prof_upload_inp" style="opacity:0;width:1px;height:1px" onchange="$('#frm_prof_upload').submit();">
                    </form>

                    <br>

                    <form action="/useredit" method="post">
                    {{csrf_field()}}
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>ユーザー名</th>
                        <td class="form-group">
                            <input type="text" name="name" value="{{old('name', Auth::user()->name)}}" class="form-control">
                            @if ($errors->has('name'))
                                <p class="bg-danger">{{$errors->first('name')}}</p>
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
