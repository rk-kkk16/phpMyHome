@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    イイヨ！！リスト
                </div>

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
                        <div style="float:left">{{$posts->links()}}</div>
                        <a href="/goodpost/add?pre_page={{$posts->currentPage()}}"><button type="button" class="btn btn-primary" style="float:right">＋</button></a>
                        <br clear="both">
                    </div>
                    <div>投稿数：{{$posts->total()}}</div>
                    <hr>

                    @forelse ($posts as $post)
                        <div class="card">
                            <div class="card-header">
                                <span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->user->name}}
                                から
                                <span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->to_user_id}})"></span> {{$post->toUser->name}}
                                へ
                            </div>
                            <div class="card-body">
                                <div id="good-pallet-{{$post->id}}" class="good-pallet">
                                    <div class="good-txt">
                                        {!!nl2br($post->body)!!}
                                    </div>
                                </div>
                                <br>
                                <p>
                                    @if (!$post->myPoint)
                                        <button id="good-btn-{{$post->id}}" class="btn btn-sm btn-primary" style="margin-right:1em" onclick="mwOpen('mw_addpoint', 'mw_addpoint', {id:{{$post->id}}});">ｲｲﾖ！！</button>
                                    @endif

                                    <a href="#" class="good-a" onclick="$('#good-point-detail-{{$post->id}}').slideToggle(); $('#good-point-btn-{{$post->id}}-a,#good-point-btn-{{$post->id}}-b').toggle(); return false">
                                        <span class="good-heart">{{$heartStr}}</span>
                                        × <span id="good-point-{{$post->id}}">{{$post->total_good}}</span>
                                        <span id="good-point-btn-{{$post->id}}-a">▼</span>
                                        <span id="good-point-btn-{{$post->id}}-b" style="display:none">▲</span>
                                    </a>
                                </p>
                                <ul class="list-group" id="good-point-detail-{{$post->id}}" style="display:none;margin-bottom:1em;">
                                @forelse ($post->points as $point)
                                    <li class="list-group-item">
                                        <span class="profimg prfmini" style="background-image:url(/users/icon/{{$point->user_id}})"></span> {{$point->user->name}}
                                        　
                                        💞 × {{$point->point}}
                                    </li>
                                @empty
                                    <li class="list-group-item noitem">まだ誰もイイヨしてません。</li>
                                @endforelse
                                </ul>

                                <p style="text-align:right"><i class="fas fa-calendar-alt"></i> {{date('Y/m/d H:i', strtotime($post->created_at))}}</p>
                            </div>
                        </div>
                        <br>
                    @empty
                        <p>投稿はありません。</p>
                    @endforelse

                    <hr>
                    <div>{{$posts->links()}}</div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ポイント付けmodal -->
<div id="mw_addpoint" class="modal-overlay">
    <div class="modal-inner">
        <p>ボタンを連打してね！ あと <span id="limit-sec"></span>秒</p>
        <p style="text-align:center">
            <button id="good-btn" disabled class="btn btn-lg btn-primary" onclick="increase()">{{$heartStr}}イイヨ！！</button>
        </p>
        <div id="heart-area" style="position:relative; width:100%; height:43vh;border:solid 1px #ccc;">
        </div>
        <input type="hidden" id="heart_point" value="0">
    </div>
</div>
<script>
var limit_sec = 3;
var now_sec = 0;
var lcked = false;
var tgt_post_id = null;
var set_intval_id = null;
let heart_str = '{{$heartStr}}';
let count_down = 3;
var now_count_down = 0;
var set_intval_countdown_id = null;

mw_activate_funcs['mw_addpoint'] = function(params) {
    tgt_post_id = params.id;
    now_sec = 0;
    now_count_down = 0;
    lcked = false;
    set_intval_id = null;
    set_intval_countdown_id = null;
    $('#limit-sec').text(limit_sec);
    $('#heart_point').val(0);
    $('#heart-area').html('');
    $('#good-btn').prop('disabled', true);
    startCountDown();
};

function startCountDown() {
    $('#heart-area').append('<div id="cntdwn"><p>' + count_down+ '</p></div>');
    set_intval_countdown_id = setInterval(function() {
        now_count_down += 1;
        if (now_count_down < count_down) {
            $('#cntdwn>p').text(count_down - now_count_down);
        } else {
            clearInterval(set_intval_countdown_id);
            $('#cntdwn').remove();
            $('#good-btn').prop('disabled', false);
            startTimer();
        }
    }, 1000);
}

function increase() {
    if (!lcked) {
        let newpoint = parseInt($('#heart_point').val()) + 1;
        $('#heart_point').val(newpoint);
        // #heart-areaの中にランダムにハートを書き込む
        let zind = newpoint + 100;
        let h_x = Math.floor(Math.random() * 100);
        let h_y = Math.floor(Math.random() * 100);
        let style_str = 'position:absolute; z-index:' + zind + '; left:' + h_x + '%; top:' + h_y + '%';
        let hrt = '<div class="one-heart" style="' + style_str + '">' + heart_str + '</div>';
        $('#heart-area').append(hrt);
    }
}

function startTimer() {
    set_intval_id = setInterval(function() {
        now_sec += 1;
        if (now_sec < limit_sec) {
            $('#limit-sec').text(limit_sec - now_sec);
        } else {
            // 送信処理を行う
            clearInterval(set_intval_id);
            lcked = true;
            $('#loading').removeClass('hidden');
            let url = '/api/goodpost/addpoint/' + tgt_post_id;
            let point = $('#heart_point').val();
            axios.put(url, {point:point})
            .then(function(result) {
                $('#loading').addClass('hidden');
                if (result.data.result == 'success') {
                    $('#good-point-' + tgt_post_id).text(result.data.new_point);
                    $('#good-btn-' + tgt_post_id).remove();
                    setGoodHeart(tgt_post_id);

                    // good-point-detailに自分を追加
                    var detail = '<li class="list-group-item"><span class="profimg prfmini" style="background-image:url(/users/icon/' + result.data.your_id + ')"></span> ' + result.data.your_name + '　';
                    detail += heart_str + ' × ' + result.data.you_added + '</li>';
                    $('#good-point-detail-' + tgt_post_id + '>.noitem').remove();
                    $('#good-point-detail-' + tgt_post_id).append(detail);
                    toastr.success('イイヨ！！');
                }
                else if (result.data.result == 'failure') {
                    toastr.error('イイヨが押されてませんでした。');
                }
                mwClose('mw_addpoint');
            })
            .catch(function(error) {
                $('#loading').addClass('hidden');
                mwClose('mw_addpoint');
                toastr.error('エラーが発生しました。');
                console.log(error);
            });
        }
    }, 1000);
}

// 表示時にテキスト表示パレット調整&ハート振りまき

// テキスト表示パレットサイズ調整
function fixGoodPalletSize(id) {
    let oriHeight = $('#good-pallet-' + id + '>.good-txt').height();
    $('#good-pallet-' + id).height(oriHeight + 100);
}

// ハート振りまき
let zIndexBase = 100;
function setGoodHeart(id) {
    $('#good-pallet-' + id + '>.good-heart-mini').remove();
    let heartCnt = parseInt($('#good-point-' + id).text());
    for (var i = 0; i < heartCnt; i++) {
        let zind = zIndexBase + i;
        let h_x = Math.floor(Math.random() * 100);
        let h_y = Math.floor(Math.random() * 100);
        let html = '<div class="good-heart-mini" style="z-index: ' + zind + '; left: ' + h_x + '%; top: ' + h_y + '%;">' + heart_str + '</div>';
        $('#good-pallet-' + id).append(html);
    }
}


window.onload = function() {
    @if (session('added'))
    mwOpen('mw_addpoint', 'mw_addpoint', {id:{{session('added')}}});
    @endif

    $('.good-pallet').each(function() {
        let palletId = $(this).attr('id').replace('good-pallet-', '');
        fixGoodPalletSize(palletId);
        setGoodHeart(palletId);
    });
};
</script>
@endsection
