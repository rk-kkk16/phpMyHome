@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">写真アップロード投稿</div>


                <div class="card-body">
                    <div>
                    <p class="crumb" style="float:left"><a href="/imagepost/?impeval={{$pre_impeval}}&tag={{$pre_tag}}&category_id={{$pre_category_id}}&page={{$pre_page}}">&lt; 写真共有</a></p>

                    @if (Auth::user()->id == $post->user_id)
                    <p style="float:right">
                        <a href="/imagepost/{{$post->id}}/edit?pre_impeval={{$pre_impeval}}&pre_tag={{$pre_tag}}&pre_category_id={{$pre_category_id}}&pre_page={{$pre_page}}"><button class="btn btn-primary" style="padding:2px 10px"><i class="far fa-edit"></i></button></a>
                        <button class="btn btn-danger" style="padding:2px 10px;margin-left:0.8em" onclick="mwOpen('mw_delete')"><i class="fas fa-trash"></i></button>
                    </p>
                    @endif
                    <br clear="both">
                    </div>

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


                    <div class="card">
                        <div class="card-header"><span class="profimg prfmini" style="background-image:url(/users/icon/{{$post->user_id}})"></span> {{$post->user->name}}</div>
                        <div class="card-body">
                            <div>
                                <img src="/imagepost/photo/{{$post->id}}?img_size=800&base_w=800" style="border:solid 1px #ccc; width:100%; max-width:800px">
                            </div>
                            <br>
                            <p>{!!nl2br(url2Link($post->title))!!}</p>
                            <p style="text-align:right"><i class="fas fa-calendar-alt"></i> {{date('Y/m/d H:i', strtotime($post->created_at))}}</p>

                            <div style="margin-bottom:1em;">
                                <imagepost-eval :import_evals="{{json_encode($post->evals)}}" :myuser_id="{{Auth::user()->id}}" :imagepost_id="{{$post->id}}" :emojis="{{json_encode($emojis)}}"/>
                            </div>

                            @if (count($post->tags))
                            <p>タグ：
                                @foreach ($post->tags as $tag)
                                    <a href="/imagepost/?tag={{$tag->tag}}" style="margin-right:1em">#{{$tag->tag}}</a>
                                @endforeach
                            </p>
                            @endif


                            <imagepost-comment :num="5" :imagepost_id="{{$post->id}}" :myuser_id="{{Auth::user()->id}}" ref="impcmnt"/>
                        </div>
                    </div>

                    <div>
                        @if ($nextpost)
                            <div style="float:left">
                                <a href="/imagepost/{{$nextpost->id}}?pre_impeval={{$pre_impeval}}&pre_page={{$pre_page}}&pre_tag={{$pre_tag}}&pre_category_id={{$pre_category_id}}">
                                <button class="btn btn-link">
                                    &lt; <img src="/imagepost/photo/{{$nextpost->id}}?img_size=40&base_w=40">
                                </button></a>
                            </div>
                        @endif


                        @if ($beforepost)
                            <div style="float:right">
                                <a href="/imagepost/{{$beforepost->id}}?pre_impeval={{$pre_impeval}}&pre_page={{$pre_page}}&pre_tag={{$pre_tag}}&pre_category_id={{$pre_category_id}}">
                                <button class="btn btn-link">
                                    <img src="/imagepost/photo/{{$beforepost->id}}?img_size=40&base_w=40"> &gt;
                                </button></a>
                            </div>
                        @endif
                        <br clear="both">
                    </div>

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


<!-- 削除確認modal -->
<div id="mw_delete" class="modal-overlay">
    <div class="modal-inner">
    <form action="/imagepost/{{$post->id}}/delete" method="post">
    {{csrf_field()}}
    <input type="hidden" name="pre_impeval" value="{{$pre_impeval}}">
    <input type="hidden" name="pre_tag" value="{{$pre_tag}}">
    <input type="hidden" name="pre_category_id" value="{{$pre_category_id}}">
    <p>この投稿を削除します。よろしいですか？</p>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" type="submit">　O K　</button>
        <button type="button" class="btn btn-secondary" onclick="mwClose('mw_delete')">Cancel</button>
    </p>
    </form>
    </div>
</div>

<!-- コメント削除確認modal -->
<div id="mw_cmntdelete" class="modal-overlay">
    <div class="modal-inner">
    <input type="hidden" id="mw_cmntdelete_id" value="">
    <p>このコメントを削除します。よろしいですか？</p>
    <pre id="mw_cmntdelete_txt"></pre>
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" onclick="app.__vue__.$refs.impcmnt.deleteComment($('#mw_cmntdelete_id').val());mwClose('mw_cmntdelete');">　O K　</button>
        <button class="btn btn-secondary" onclick="mwClose('mw_cmntdelete')">Cancel</button>
    </p>
    </div>
</div>
<script>
mw_activate_funcs['mw_cmntdelete'] = function(params) {
    $('#loading').removeClass('hidden');
    let id = params.id;
    let url = '/api/imagepost/comment/' + id;
    axios.get(url).then(function(response) {
        $('#loading').addClass('hidden');
        let cmnt = response.data.data;
        $('#mw_cmntdelete_txt').text(cmnt.comment);
        $('#mw_cmntdelete_id').val(cmnt.id);
    })
    .catch(error => {
        $('#loading').addClass('hidden');
        mwClose('mw_cmntdelete');
        alert('エラーが発生しました。');
    });
}
</script>
@endsection
