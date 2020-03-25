window.toastr = require('toastr');
toastr.options = {
    positionClass: 'toast-top-full-width',
    timeOut: 3000,
};

// エラーの場合
if($('.validate-error').length){
    toastr['warning']('エラーが発生しました');
}

