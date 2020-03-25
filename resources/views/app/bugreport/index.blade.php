@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">バグ報告・要望リスト</div>

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
                        <div style="float:left">
                            <a href="/bugreport?state=yet&sort_key={{$sort_key}}&sort_order={{$sort_order}}" class="link-btn {{$actives['yet']}}">未対応</a>
                            | <a href="/bugreport?state=done&sort_key={{$sort_key}}&sort_order={{$sort_order}}" class="link-btn {{$actives['done']}}">対応済</a>
                        </div>

                        <div style="float:left; margin-left:1em">
                            <a href="/bugreport?state={{$state}}&sort_key=level&sort_order={{$sort_order}}" class="link-btn {{$actives['level']}}">緊急度(分類)順</a>
                            / <a href="/bugreport?state={{$state}}&sort_key=created_at&sort_order={{$sort_order}}" class="link-btn btn-link {{$actives['created_at']}}">登録順</a>
                        </div>
                        <div style="float:left; margin-left:1em">
                            <a href="/bugreport?state={{$state}}&sort_key={{$sort_key}}&sort_order=asc" class="link-btn {{$actives['asc']}}">▲</a>
                            / <a href="/bugreport?state={{$state}}&sort_key={{$sort_key}}&sort_order=desc" class="link-btn {{$actives['desc']}}">▼</a>
                        </div>

                        <div style="float:right">
                            <button onclick="if ($('#addform').css('display') == 'none') { $('#addform').show();mwEditInit(); }" class="btn btn-primary" style="padding:2px 10px;">＋</button>
                        </div>

                        <br clear="both">

                        <!-- add form -->
                        <div id="addform" style="display:none">
                            <hr>
                            <p>○ 新規報告</p>
                            <input id="mw_edit_id" type="hidden" value="">
                            <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th>件名</th>
                                    <td class="form-group">
                                        <input id="mw_edit_subject" type="text" value="" class="form-control">
                                        <div id="mw_edit_subject_err"></div>
                                    </td>
                                </tr><tr>
                                    <th>分類</th>
                                    <td class="form-group">
                                        <select id="mw_edit_level" class="form-control">
                                            <option value="5">不具合</option>
                                            <option value="3">変更</option>
                                            <option value="1">機能追加</option>
                                        </select>
                                    </td>
                                </tr><tr>
                                    <th colspan="2">説明</th>
                                </tr><tr>
                                    <td colspan="2" class="form-group">
                                        <textarea id="mw_edit_description" class="form-control" style="height:10em"></textarea>
                                        <div id="mw_edit_description_err"></div>
                                    </td>
                                </tr><tr>
                            </tbody>
                            </table>
                            <br>
                            <p style="width:100%;text-align:center">
                                <button class="btn btn-primary" onclick="bugReportSave();">　O K　</button>
                                <button class="btn btn-secondary" onclick="$('#addform').hide();">Cancel</button>
                            </p>
                        </div>
                        <!-- /add form -->

                    </div>
                    <hr style="margin-top:10px;">

                    <bug-report-list num=15 state="{{$state}}" sort_key="{{$sort_key}}" sort_order="{{$sort_order}}" ref="bugreportlist"></bug-report-list>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 追加・編集モーダル -->
<script>
function mwEditInitErr(id) {
    var idsuf = '';
    if (id) {
        idsuf = '_' + id;
    }
    mwShowErrMsg('mw_edit_subject_err' + idsuf ,'');
    mwShowErrMsg('mw_edit_description_err' + idsuf , '');
}

// 編集保存
function bugReportEditSave(id) {
    mwEditInitErr(id);
    $('#loading').removeClass('hidden');
    let props = {
        id: id,
        subject: $('#edit_subject_' + id).val(),
        description: $('#edit_description_' + id).val(),
        level: $('#edit_level_' + id).val(),
    };
    let url = '/api/bugreport/regist';

    // edit
    axios.put(url, props).then(function(response) {
        $('#loading').addClass('hidden');
        if (typeof(response.data.errors) != 'undefined') {
            for (var err_ele in response.data.errors) {
                mwShowErrMsg('mw_edit_' + err_ele + '_err_' + id, response.data.errors[err_ele]);
            }
            return;
        }

        app.__vue__.$refs.bugreportlist.callNewItem(response.data.data);
        toastr.success('変更を保存しました。');
        $('#edit_' + id).hide();
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        console.log(error);
        alert('エラーが発生しました。');
        return;
    });
}

// 新規保存
function bugReportSave() {
    mwEditInitErr(0);
    $('#loading').removeClass('hidden');
    let props = {
        id: 0,
        subject: $('#mw_edit_subject').val(),
        description: $('#mw_edit_description').val(),
        level: $('#mw_edit_level').val(),
    };
    let url = '/api/bugreport/regist';

    axios.post(url, props).then(function(response) {
        $('#loading').addClass('hidden');
        if (typeof(response.data.errors) != 'undefined') {
            for (var err_ele in response.data.errors) {
                mwShowErrMsg('mw_edit_' + err_ele + '_err', response.data.errors[err_ele]);
            }
            return;
        }

        app.__vue__.$refs.bugreportlist.callNewItem(response.data.data);
        toastr.success('追加しました。');
        $('#addform').hide();
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        console.log(error);
        alert('エラーが発生しました。');
        return;
    });
}

function mwEditInit() {
    mwEditInitErr();
    $('#mw_edit_subject').val('');
    $('#mw_edit_description').val('');
    $('#mw_edit_level').val(5);
}

</script>


<!-- 削除確認モーダル -->
<div id="mw_delete" class="modal-overlay">
    <div class="modal-inner">
    <input type="hidden" id="mw_delete_id" value="">
    <p>この報告を削除します。よろしいですか？</p>
    <p>件名：<span id="mw_delete_subject"></span></p>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" onclick="bugReportDelete($('#mw_delete_id').val());">　O K　</button>
        <button class="btn btn-secondary" onclick="mwClose('mw_delete')">Cancel</button>
    </p>
    </div>
</div>
<script>
mw_activate_funcs['mw_delete'] = function(params) {
    $('#loading').removeClass('hidden');
    let id = params.id;
    let url = '/api/bugreport/detail/' + id;
    axios.get(url).then(function(response) {
        $('#loading').addClass('hidden');
        let item = response.data.data;
        $('#mw_delete_subject').text(item.subject);
        $('#mw_delete_id').val(item.id);
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        mwClose('mw_delete');
        alert('エラーが発生しました。');
    });
}

// アイテム削除実施
function bugReportDelete(id) {
    $('#loading').removeClass('hidden');
    mwClose('mw_delete');
    let url = '/api/bugreport/delete/' + id;
    axios.delete(url).then(function(response) {
        app.__vue__.$refs.bugreportlist.callDeleteItem(id);
        $('#loading').addClass('hidden');
        toastr.success('削除しました。');
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        alert('エラーが発生しました。');
    });
}
</script>
<!-- -->
@endsection

