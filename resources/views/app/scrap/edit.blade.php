@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @if ($post->id)
                        スクラップブック投稿編集
                    @else
                        スクラップブック新規投稿
                    @endif
                </div>


                <div class="card-body">
                    <p class="crumb">
                        <a href="/scrap/">&lt; スクラップブック</a>
                        @if ($post->id)
                            <a href="/scrap/{{$post->id}}">&lt; 投稿</a>
                        @endif
                    </p>

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


                    <form action="/api/scrap/upload" id="frm_upload" method="post" enctype="multipart/form-data">
                    <input type="file" name="upload" id="frm_upload_inp" style="opacity:0;width:1px;height:1px" onchange="uploadToApi()">
                    </form>

                    <form method="post"
                        @if ($post->id)
                            action="/scrap/edit/{{$post->id}}"
                        @else
                            action="/scrap/add"
                        @endif
                    >
                    {{csrf_field()}}

                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>タイトル</th>
                    </tr><tr>
                        <td class="form-group">
                            <input type="text" name="subject" value="{{$subject}}" class="form-control">
                            @if ($errors->has('subject'))
                                <p class="bg-danger">{{$errors->first('subject')}}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>カテゴリ</th>
                    </tr><tr>
                        <td class="form-group">
                            <select name="sc_category_id" class="form-control">
                                @foreach ($categorys as $category)
                                    <option
                                        value="{{$category->id}}"
                                        @if ($category->id == $sc_category_id)

                                            selected
                                        @endif
                                    >
                                        {{$category->category_name}}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('sc_category_id'))
                                <p class="bg-danger">{{$errors->first('sc_category_id')}}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>本文</th>
                    </tr><tr>
                        <td class="form-group">
                            <textarea name="body" class="form-control" style="height:30vh">{{$body}}</textarea>
                            @if ($errors->has('body'))
                                <p class="bg-danger">{{$errors->first('body')}}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>ファイルアップロード({{$max_upload_file}}ファイルまで)</th>
                    </tr><tr>
                        <td>
                            <div id="uploaded_imgs">
                            </div>
                            <div id="uploaded_files">
                            </div>
                            <br>
                            <p>※5MB以下、画像は縦横サイズ5000px以下</p>
                            <div>
                                <button type="button" id="upload_btn" class="btn btn-primary" onclick="$('#frm_upload_inp').click();">アップロード</button>
                                @if ($errors->has('upload.0'))
                                <p class="bg-danger">{{$errors->first('upload.0')}}</p>
                                @endif
                                <p id="upload_err" class="bg-danger hidden"></p>
                            </div>
                        </td>
                    </tr>
                    @if ($post->id)
                    <tr>
                        <th>アップロード済ファイル</th>
                    </tr><tr>
                        <td>
                            todo ScFilesを並べて表示、削除ボタン
                            <div id="scfile_imgs"></div>
                            <div id="scfile_files"></div>
                        </td>
                    </tr>
                    @endif

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

// アップロードファイル数に基づいてupload_btnのdiable切替
function chkUploadEnable() {
    let max_upload = {{$max_upload_file}};
    let cnt = $('#uploaded_imgs>div').length
            + $('#uploaded_files>div').length
            + $('#scfile_imgs>div').length
            + $('#scfile_files>div').length;
console.log(cnt);
    if (max_upload - cnt > 0) {
        $('#upload_btn').prop('disabled', false);
    } else {
        $('#upload_btn').prop('disabled', true);
    }
}

// axiosでファイルアップロード /api/scrap/upload へ
function uploadToApi() {
    $('#upload_err').text('').hide();

    var params = new FormData();
    params.append('upload', $('#frm_upload_inp').prop('files')[0]);
    axios.post('/api/scrap/upload', params)
        .then(function(response) {
            if (response.data.result == 'ok') {
                let path = response.data.uploaded_file;
                let file_name = response.data.original_file_name;
                let is_image = response.data.is_image;
                makeUploadedDom(path, file_name, is_image);
            } else {
                $('#upload_err').text(response.data.errors).show();
            }
        })
        .catch(function(error) {
            console.log(error);
            alert('エラーが発生しました。');
        });
}

function deleteTmpFile(path, obj) {
    let url = '/api/scrap/tmp/delete?uploaded_file=' + encodeURIComponent(path);
    axios.delete(url)
    .then(function(response) {
        $(obj).parent().parent().remove();
        chkUploadEnable();
    })
    .catch(function(error) {
        console.error(error);
        alert('エラーが発生しました。');
    });
}

function makeUploadedDom(path, file_name, is_image) {
    var html = '<div>';

    if (is_image) {
        html += '<img src="' + path + '" style="border:solid 1px #ccc; max-width:300px; max-height:300px;">';
        html += '<p><button type="button" class="btn btn-secondary" onclick="deleteTmpFile(\'' + path + '\', this)">削除</button></p>';
    } else {
        html += '<p><i class="fas fa-file"></i> ' + file_name + ' <button type="button" class="btn btn-secondary" onclick="deleteTmpFile(\'' + path + '\', this)">削除</button></p>';
    }

    let paramstr = path + ';' + file_name + ';' + is_image;
    html += '<input type="hidden" name="upload[]" value="' + paramstr + '">';
    html += '</div>';

    if (is_image) {
        $('#uploaded_imgs').append(html);
    } else {
        $('#uploaded_files').append(html);
    }
    chkUploadEnable();
}

window.onload = function() {
    // アップロード済tmpファイルの表示処理
    @foreach ($uploads as $off => $upload)
        let upload_str = '{{$upload}}';
        let ary = upload_str.split(/;/g);
        makeUploadedDom(ary[0], ary[1], ary[2]);
    @endforeach
};

</script>
@endsection
