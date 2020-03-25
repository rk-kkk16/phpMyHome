@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">写真アップロード投稿</div>


                <div class="card-body">
                    <p class="crumb"><a href="/imagepost/?impeval={{$pre_impeval}}&tag={{$pre_tag}}&category_id={{$pre_category_id}}&page={{$pre_page}}">&lt; 写真共有</a></p>

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


                    <form action="/api/imagepost/upload" id="frm_upload" method="post" enctype="multipart/form-data">
                    <input type="file" name="upload_img" id="frm_upload_inp" style="opacity:0;width:1px;height:1px" onchange="uploadToApi()">
                    </form>

                    <form method="post" action="/imagepost/add">
                    {{csrf_field()}}
                    <input type="hidden" id="upload_img_apply"  name="upload_img" value="{{$upload_img}}">
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>写真アップロード</th>
                    </tr><tr>
                        <td>
                            <div id="uploaded_img">
                                <p>アップロードする写真を選択してください。</p>
                            </div>
                            <br>
                            <p>※5MB以下、縦横サイズ5000px以下</p>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="$('#frm_upload_inp').click();">アップロード</button>
                                @if ($errors->has('upload_img'))
                                <p class="bg-danger">{{$errors->first('upload_img')}}</p>
                                @endif
                                <p id="upload_err" class="bg-danger hidden"></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>キャプション(1000文字以内)</th>
                    </tr><tr>
                        <td class="form-group">
                            <textarea name="title" class="form-control">{{old('title', '')}}</textarea>
                            @if ($errors->has('title'))
                                <p class="bg-danger">{{$errors->first('title')}}</p>
                            @endif
                        </td>
                    </tr>
<!--
                    <tr>
                        <th>カテゴリ</th>
                    </tr><tr>
                        <td class="form-group">
                            <select name="imp_category_id" class="form-control">
                                <option value="0">(指定なし)</option>
                                @foreach ($categorys as $category)
                                    <option
                                        value="{{$category->id}}"
                                        @if ($category->id == $imp_category_id)

                                            selected
                                        @endif
                                    >
                                        {{$category->category_name}}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('imp_category_id'))
                                <p class="bg-danger">{{$errors->first('imp_category_id')}}</p>
                            @endif
                        </td>
                    </tr>
-->
                    <tr>
                        <th>タグ(100文字以内で入力)</th>
                    </tr><tr>
                        <td class="form-group">
                            <p>スペース,改行で区切って入力してください。(※半角記号は不可)</p>
                            <textarea name="tagtext" class="form-control" style="height:4em">{{$tagtext}}</textarea>
                            @if ($errors->has('tagtext'))
                                <p class="bg-danger">{{$errors->first('tagtext')}}</p>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    </table>

                    <div class="text-center"><button type="submit" class="btn btn-primary">投　稿</button></div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

// axiosでファイルアップロード /api/imagepost/upload へ
function uploadToApi() {
    $('#upload_err').text('').hide();

    var params = new FormData();
    params.append('upload_img', $('#frm_upload_inp').prop('files')[0]);
    axios.post('/api/imagepost/upload', params)
        .then(function(response) {
            console.log(response);
            if (response.data.result == 'ok') {
                $('#uploaded_img').html('<img src="' + response.data.uploaded_file + '" style="max-width:300px; max-height:300px">');
                $('#upload_img_apply').val(response.data.uploaded_file);
            } else {
                $('#upload_err').text(response.data.errors).show();
            }
        })
        .catch(function(error) {
            console.log(error);
            alert('エラーが発生しました。');
        });
}

window.onload = function() {
    if ($('#upload_img_apply').val()) {
        $('#uploaded_img').html('<img src="' + $('#upload_img_apply').val() + '" style="max-width:300px; max-height:300px">');
    }
};

</script>
@endsection
