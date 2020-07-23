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
                        <a href="/scrap/?{{$paramstr}}">&lt; スクラップブック</a>
                        @if ($post->id)
                            <a href="/scrap/{{$post->id}}?{{$in_paramstr}}">&lt; 投稿</a>
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
                            action="/scrap/edit/{{$post->id}}?{{$in_paramstr}}"
                        @else
                            action="/scrap/add?{{$in_paramstr}}"
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
                            <select name="sc_category_id" id="sc_category_id" class="form-control" style="margin-bottom:0.5em" onchange="switchChildChkArea()">
                                @foreach ($categorys as $category)
                                    <option
                                        value="{{$category->id}}"
                                        @if (!$category->is_primary)
                                            data-type="top-category"
                                        @endif
                                        @if ($category->id == $sc_category_id)

                                            selected
                                        @endif
                                    >
                                        {{$category->category_name}}
                                    </option>
                                    @if (count($category->childs) > 0)
                                        @foreach ($category->childs as $child)
                                            <option
                                                value="{{$child->id}}"
                                                @if ($child->id == $sc_category_id)

                                                    selected
                                                @endif
                                            >
                                                {{$category->category_name}} &gt; {{$child->category_name}}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                            @if ($errors->has('sc_category_id'))
                                <p class="bg-danger">{{$errors->first('sc_category_id')}}</p>
                            @endif

                            <p id="add-cate-area-opener">
                                <a href="#" class="btn btn-link" onclick="$('#add-cate-area-opener').hide();$('#add-cate-area').show();return false;">
                                    <i class="fas fa-plus"></i> カテゴリ追加
                                </a>
                            </p>
                            <div id="add-cate-area" style="display:none" class="row">
                                <div class="col-1">
                                <a href="#" class="btn btn-link" onclick="$('#add-cate-area-opener').show();$('#add-cate-area').hide();return false;">
                                    <i class="fas fa-minus"></i>
                                </a>
                                </div>

                                <div class="col-11" style="padding-top:4px">
                                    <div class="form-group">
                                        <label>カテゴリ名</label>
                                        <input type="text" id="new_category_name" class="form-control">
                                        <p id="new_category_name_err" class="bg-danger" style="display:none"></p>
                                    </div>
                                    <div class="form-group">
                                        <p id="child-chk-area" style="float:left"><label>
                                            <input type="checkbox" id="is_child" value="1">子カテゴリ
                                        </label></p>

                                        <p style="float:right">
                                            <button type="button" class="btn btn-secondary" onclick="postAddCategory()">カテゴリ登録</button>
                                        </p>
                                        <br clear="both">
                                    </div>
                                </div>
                            </div>
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

    let chked = $('input[name*="delfile"]:checked').length;

    if (max_upload - cnt + chked > 0) {
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

// ScFileの表示
function makeScFileDom(id, file_path, file_name, is_image, checked) {
    var html = '<div>';
    html += '<p>';
    html += '<input type="checkbox" name="delfile[' + id + ']" value="' + id + '" id="delfile_' + id + '" ' + checked + ' onclick="chkUploadEnable()"><label for="delfile_' + id + '">削除</label> ';
    if (is_image) {
        html += '<img src="' + file_path + '" style="border:solid 1px #ccc; max-width:300px; max-height:300px;">';
    } else {
        html += '<i class="fas fa-file"></i> ' + file_name;
    }
    html += '</p>';
    html += '</div>';
    if (is_image) {
        $('#scfile_imgs').append(html);
    } else {
        $('#scfile_files').append(html);
    }
    chkUploadEnable();
}

window.onload = function() {
    // アップロード済tmpファイルの表示処理
    var uploadeds = [];
    @foreach ($uploads as $off => $upload)
        uploadeds.push('{{$upload}}');
    @endforeach
    for (var i = 0; i < uploadeds.length; i++) {
        let ary = uploadeds[i].split(/;/g);
        makeUploadedDom(ary[0], ary[1], ary[2]);
    }

    // 編集時 ScFileの表示処理
    @if ($post->id)
        var scfiles = [];
        @foreach ($post->files as $scfile)
            scfiles.push({
                id:{{$scfile->id}},
                file_path:'/storage/scrap/{{$scfile->id_range}}/{{$post->id}}/{{$scfile->id}}.{{$scfile->file_type}}',
                file_name:'{{$scfile->file_name}}',
                is_image: {{$scfile->is_image}},
            @if (isset($scfile_delchks[$scfile->id]))
                chked:'checked',
            @else
                chked:'',
            @endif
            });
        @endforeach
        for (var i = 0; i < scfiles.length; i++) {
            let scfile = scfiles[i];
            makeScFileDom(scfile.id, scfile.file_path, scfile.file_name, scfile.is_image, scfile.chked);
        }
    @endif
};

// 選択したカテゴリによってchild-chk-areaの表示/非表示切替
function switchChildChkArea() {
    let nowSelectedId = $('#sc_category_id').val();
    $('#sc_category_id>option').each(function() {
        if ($(this).prop('selected')) {
            if ($(this).attr('data-type') == 'top-category') {
                $('#child-chk-area').show();
            } else {
                $('#child-chk-area').hide();
            }
            return;
        }
    });
}

// カテゴリ追加POST送信
function postAddCategory() {
    $('#loading').removeClass('hidden');
    $('#new_category_name_err').text('').hide();
    var params = {category_name:$('#new_category_name').val()};
    if ($('#child-chk-area').css('display') != 'none'
        && $('#is_child').prop('checked')) {
        params['parent_category_id'] = $('#sc_category_id').val();
    }
    axios.post('/api/scrap/category/add', params)
    .then(function(response) {
        if (response.data.result == 'error') {
            if (typeof(response.data.errors.category_name) != 'undefined') {
                $('#new_category_name_err').text(response.data.errors.category_name).show();
            }
            toastr.error('入力不備があります。');
        } else {
            let new_category_id = response.data.data.id;
            let new_category_name = response.data.data.category_name;
            var new_parent_id = 0;
            if (response.data.data.parent_category_id) {
                new_parent_id = response.data.data.parent_category_id;
            }
            $('#new_category_name').val('');
            $('#is_child').prop('checked', false);
            if (new_parent_id != 0) {
                $('#sc_category_id>option').each(function() {
                    if ($(this).attr('value') == new_parent_id) {
                        let parent_category_name = $(this).text();
                        let optionTag = '<option value="' + new_category_id + '">' + parent_category_name + ' &gt; ' + new_category_name + '</option>';
                        $(this).after(optionTag);
                        $('#sc_category_id').val(new_category_id);
                        return;
                    }
                });
            } else {
                let optionTag = '<option value="' + new_category_id + '" data-type="top-category">' + new_category_name + '</option>';
                $('#sc_category_id').append(optionTag);
                $('#sc_category_id').val(new_category_id);
            }
            toastr.success('カテゴリ追加しました。');
        }
        $('#loading').addClass('hidden');
    })
    .catch(function(error) {
        console.log(error);
        alert('エラーが発生しました。');
        $('#loading').addClass('hidden');
    });
}

</script>
@endsection
