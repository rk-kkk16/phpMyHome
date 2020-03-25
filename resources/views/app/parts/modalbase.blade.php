<script>
// モーダルウィンドウ起動時の関数を格納する配列
var mw_activate_funcs = [];

// モーダルウィンドウの起動
function mwOpen(id, activate_func_name, activate_func_params) {
    // 重複起動チェック
    $('.modal-overlay').each(function() {
        if ($(this).hasClass('modal-active')) {
            return;
        }
    });

    $('#' + id).addClass('modal-active').show();
    $('#' + id + '>.modal-inner').show();

    // 閉じるボタン挿入
    $('#' + id + '>.modal-inner').prepend('<p class="modal-close-area"><button class="btn btn-link" onclick="mwClose(\''+id+'\')">X</button></p>');

    $(window).resize(mwCenteringModalSyncer(id)) ;
    if (typeof(mw_activate_funcs[activate_func_name]) != 'undefined') {
        mw_activate_funcs[activate_func_name](activate_func_params);
    }
}

// モーダルウィンドウ閉じる
function mwClose(id) {
    $('#' + id).removeClass('modal-active').hide();
    $('#' + id + '>.modal-inner').hide();
    $('.modal-close-area').remove();
    $(window).resize('');
}

// ウィンドウリサイズ時にセンタリングを実行する関数
function mwCenteringModalSyncer(id) {
    //画面(ウィンドウ)の幅、高さを取得
    var w = $( window ).width() ;
    var h = $( window ).height() ;

    // コンテンツ(#modal-content)の幅、高さを取得
    var cw = $('#' + id).outerWidth();
    var ch = $('#' + id).outerHeight();

    //センタリングを実行する
    $('#' + id).css( {"left": ((w - cw)/2) + "px","top": ((h - ch)/2) + "px"} ) ;
}

// APIでの登録・編集エラーメッセージを出力する
function mwShowErrMsg(id, msg) {
    if (!msg) {
        $('#' + id).html('');
    } else {
        $('#' + id).html('<p class="bg-danger">' + msg + '</p>');
    }
}
</script>
