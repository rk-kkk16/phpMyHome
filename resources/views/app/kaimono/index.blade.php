@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">買い物リスト</div>

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
                            <a href="/kaimono?state=yet&sort_key={{$sort_key}}&sort_order={{$sort_order}}" class="link-btn {{$actives['yet']}}">買わなきゃ</a>
                            | <a href="/kaimono?state=done&sort_key={{$sort_key}}&sort_order={{$sort_order}}" class="link-btn {{$actives['done']}}">買った</a>
                        </div>

                        <div style="float:left; margin-left:1em">
                            <a href="/kaimono?state={{$state}}&sort_key=level&sort_order={{$sort_order}}" class="link-btn {{$actives['level']}}">優先度順</a>
                            / <a href="/kaimono?state={{$state}}&sort_key=created_at&sort_order={{$sort_order}}" class="link-btn btn-link {{$actives['created_at']}}">登録順</a>
                        </div>
                        <div style="float:left; margin-left:1em">
                            <a href="/kaimono?state={{$state}}&sort_key={{$sort_key}}&sort_order=asc" class="link-btn {{$actives['asc']}}">▲</a>
                            / <a href="/kaimono?state={{$state}}&sort_key={{$sort_key}}&sort_order=desc" class="link-btn {{$actives['desc']}}">▼</a>
                        </div>

                        <div style="float:right">
                            <button onclick="mwOpen('mw_edit','mw_edit',{id:0})" class="btn btn-primary" style="padding:2px 10px;">＋</button>
                        </div>

                        <br clear="both">
                    </div>
                    <hr style="margin-top:10px;">

                    <kaimono-list num=15 state="{{$state}}" sort_key="{{$sort_key}}" sort_order="{{$sort_order}}" ref="kaimonolist"></kaimono-list>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 追加・編集モーダル -->
<div id="mw_edit" class="modal-overlay">
    <div class="modal-inner">
    <p>○<span id="mw_edit_title"></span></p>
    <input id="mw_edit_id" type="hidden" value="">
    <table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <th>商品名</th>
            <td class="form-group">
                <input id="mw_edit_item_name" type="text" value="" class="form-control">
                <div id="mw_edit_item_name_err"></div>
            </td>
        </tr><tr>
            <th>個数</th>
            <td class="form-group">
                <input id="mw_edit_quantity" type="number" value="1" maxlength="3" max="999" class="form-control">
                <div id="mw_edit_quantity_err"></div>
            </td>
        </tr><tr>
            <th>お急ぎ度</th>
            <td class="form-group" id="mw_edit_level_td" class="star_str_lv3" style="font-size:2em">
                <span id="mw_edit_lv_1" onclick="mwEditSetLevel(1)">★</span>
                <span id="mw_edit_lv_2" onclick="mwEditSetLevel(2)">☆</span>
                <span id="mw_edit_lv_3" onclick="mwEditSetLevel(3)">☆</span>
                <span id="mw_edit_lv_4" onclick="mwEditSetLevel(4)">☆</span>
                <span id="mw_edit_lv_5" onclick="mwEditSetLevel(5)">☆</span>
                <input id="mw_edit_level" type="hidden" value="3">
            </td>
        </tr>
    </tbody>
    </table>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" onclick="mustbuySave();">　O K　</button>
        <button class="btn btn-secondary" onclick="mwClose('mw_edit')">Cancel</button>
    </p>
    </div>
</div>
<script>
function mwEditInitErr() {
    mwShowErrMsg('mw_edit_item_name_err','');
    mwShowErrMsg('mw_edit_quantity_err', '');
}

function mustbuySave() {
    mwEditInitErr();
    $('#loading').removeClass('hidden');
    let props = {
        id: 0,
        item_name: $('#mw_edit_item_name').val(),
        quantity: $('#mw_edit_quantity').val(),
        level: $('#mw_edit_level').val(),
    };
    let url = '/api/mustbuys/regist';

    if ($('#mw_edit_id').val() > 0) {
        // edit
        props.id = $('#mw_edit_id').val();
        axios.put(url, props).then(function(response) {
            $('#loading').addClass('hidden');
            if (typeof(response.data.errors) != 'undefined') {
                for (var err_ele in response.data.errors) {
                    mwShowErrMsg('mw_edit_' + err_ele + '_err', response.data.errors[err_ele]);
                }
                return;
            }

            app.__vue__.$refs.kaimonolist.callNewItem(response.data.data);
            toastr.success('変更を保存しました。');
            mwClose('mw_edit');
        })
        .catch(error => {
            $('#loading').addClass('hidden');
            console.log(error);
            alert('エラーが発生しました。');
            return;
        });
    } else {
        // new
        axios.post(url, props).then(function(response) {
            $('#loading').addClass('hidden');
            if (typeof(response.data.errors) != 'undefined') {
                for (var err_ele in response.data.errors) {
                    mwShowErrMsg('mw_edit_' + err_ele + '_err', response.data.errors[err_ele]);
                }
                return;
            }

            app.__vue__.$refs.kaimonolist.callNewItem(response.data.data);
            toastr.success('追加しました。');
            mwClose('mw_edit');
        })
        .catch(error => {
            $('#loading').addClass('hidden');
            console.log(error);
            alert('エラーが発生しました。');
            return;
        });
    }
}

mw_activate_funcs['mw_edit'] = function(params) {
    $('#mw_edit_id').val(params.id);
    mwEditInitErr();
    if (params.id > 0) {
        $('#mw_edit_title').text('アイテム編集');
        $('#loading').removeClass('hidden');
        let url = '/api/mustbuys/detail/' + params.id;
        axios.get(url).then(function(response) {
            $('#loading').addClass('hidden');
            let item = response.data.data;
            $('#mw_edit_item_name').val(item.item_name);
            $('#mw_edit_quantity').val(item.quantity);
            $('#mw_edit_level').val(item.level);
            mwEditSetLevel($('#mw_edit_level').val());
        })
        .catch(error => {
            $('#loading').addClass('hidden');
            mwClose('mw_edit');
            alert('エラーが発生しました。');
        });
    } else {
        $('#mw_edit_title').text('アイテム登録');
        $('#mw_edit_item_name').val('');
        $('#mw_edit_quantity').val(1);
        $('#mw_edit_level').val(3);
        mwEditSetLevel($('#mw_edit_level').val());
    }
}

function mwEditSetLevel(lv) {
    let before_lv = $('#mw_edit_level').val();
    $('#mw_edit_level_td').removeClass('star_str_lv' + before_lv).addClass('star_str_lv' + lv);
    $('#mw_edit_level').val(lv);
    for (var l = 1; l <= 5; l++) {
        var instr = '☆';
        if (l <= lv) {
            instr = '★';
        }
        $('#mw_edit_lv_' + l).text(instr);
    }
}
</script>


<!-- 削除確認モーダル -->
<div id="mw_delete" class="modal-overlay">
    <div class="modal-inner">
    <input type="hidden" id="mw_delete_item_id" value="">
    <p>このアイテムを削除します。よろしいですか？</p>
    <p>品名：<span id="mw_delete_item_name"></span></p>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" onclick="mustbuyDelete($('#mw_delete_item_id').val());">　O K　</button>
        <button class="btn btn-secondary" onclick="mwClose('mw_delete')">Cancel</button>
    </p>
    </div>
</div>
<script>
/** 削除機能は一旦非表示 **/
mw_activate_funcs['mw_delete'] = function(params) {
    $('#loading').removeClass('hidden');
    let id = params.id;
    let url = '/api/mustbuys/detail/' + id;
    axios.get(url).then(function(response) {
        $('#loading').addClass('hidden');
        let item = response.data.data;
        $('#mw_delete_item_name').text(item.item_name);
        $('#mw_delete_item_id').val(item.id);
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        mwClose('mw_delete');
        alert('エラーが発生しました。');
    });
}

// アイテム削除実施
function mustbuyDelete(id) {
    $('#loading').removeClass('hidden');
    mwClose('mw_delete');
    let url = '/api/mustbuys/delete/' + id;
    axios.delete(url).then(function(response) {
        app.__vue__.$refs.kaimonolist.callDeleteItem(id);
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

