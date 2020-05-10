@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">イイヨ！！投稿</div>


                <div class="card-body">
                    <p class="crumb"><a href="/goodpost/?page={{$pre_page}}">&lt; イイヨ！！リスト</a></p>
                    <br>

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


                    <form method="post" action="/goodpost/add?pre_page={{$pre_page}}" id="addfrm">
                    {{csrf_field()}}
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>宛先</th>
                        <td>
                            <p style="border:solid 1px #ccc; padding:3px">
                                <a href="#" onclick="$('#select-users').slideToggle(); $('#sel-btn-a,#sel-btn-b').toggle(); return false;">
                                <span id="selected-user">
                                    選択してください
                                </span>
                                <button id="sel-btn-a" class="btn btn-sm" style="margin-left:1em">▼</button>
                                <button id="sel-btn-b" class="btn btn-sm" style="margin-left:1em;display:none">▲</button>
                                </a>
                            </p>
                            <ul id="select-users" class="list-group" style="display:none">
                                @foreach ($users as $user)
                                    <li id="user-{{$user->id}}" class="list-group-item">
                                        <a href="#" onclick="$('#to_user_id').val({{$user->id}}); setSelectedUser(); $('#select-users').slideToggle(); $('#sel-btn-a,#sel-btn-b').toggle(); return false;" style="display:block">
                                        <span class="profimg prfmini" style="background-image:url(/users/icon/{{$user->id}})"></span> {{$user->name}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" id="to_user_id" name="to_user_id" value="{{old('to_user_id', '')}}">
                            @if ($errors->has('to_user_id'))
                                <p class="bg-danger">{{$errors->first('to_user_id')}}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">よかったこと(100文字以内)</th>
                    </tr><tr>
                        <td colspan="2" class="form-group">
                            <textarea name="body" class="form-control" style="height:25vh">{{old('body', '')}}</textarea>
                            @if ($errors->has('body'))
                                <p class="bg-danger">{{$errors->first('body')}}</p>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    </table>

                    <div class="text-center"><button type="submit" class="btn btn-primary" onclick="$(this).prop('disabled', true); $('#addfrm').submit();">投　稿</button></div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

function setSelectedUser() {
    let selected_user_id = $('#to_user_id').val();
    if (!selected_user_id) {
        return false;
    }
    let html = $('#user-' + selected_user_id + '>a').html();
    $('#selected-user').html(html);
}

window.onload = function() {
    setSelectedUser();
};

</script>
@endsection
