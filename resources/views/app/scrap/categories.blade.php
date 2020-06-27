@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">カテゴリ管理</div>


                <div class="card-body">
                    <p class="crumb">
                        <a href="/scrap/">&lt; スクラップブック</a>
                    </p>
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


                    <ul class="list-group">
                        @forelse ($categories as $category)
                            <li class="list-group-item">
                                <div class="li-left">
                                    <i class="fa fa-list-alt"></i> <span id="cate_name_{{$category->id}}">{{$category->category_name}}</span>
                                </div>
                                <div class="li-right">
                                    @if ($category->is_primary)
                                        <button type="button" class="btn btn-primary" style="padding:2px 10px" onclick="mwOpen('mw_addCategory', 'mw_addCategory', {id:0});">＋</button>
                                    @else
                                        <button type="button" class="btn btn-primary" style="padding:2px 10px" onclick="mwOpen('mw_addCategory', 'mw_addCategory', {id:{{$category->id}}});">＋</button>
                                        <button type="button"
                                            onclick="mwOpen('mw_editCategory', 'mw_editCategory', {id:{{$category->id}}});"
                                            class="btn btn-secondary"
                                            style="padding:2px 10px; margin-left:0.8em"
                                            @if ($category->user->id != Auth::user()->id)
                                                disabled
                                            @endif
                                        ><i class="far fa-edit"></i></button>
                                        <button type="button"
                                            onclick="mwOpen('mw_deleteCategory', 'mw_deleteCategory', {id:{{$category->id}}});"
                                            class="btn btn-danger"
                                            style="padding:2px 10px; margin-left:0.8em"
                                            @if (count($category->childs) || $category->user->id != Auth::user()->id)
                                                disabled
                                            @endif
                                        ><i class="fas fa-trash"></i></button>
                                    @endif
                                </div>
                                <br clear="both">
                                @if (count($category->childs))
                                    <br>
                                    <ul class="list-group">
                                        @foreach ($category->childs as $child)
                                            <li class="list-group-item">
                                                <div class="li-left">
                                                    <i class="fa fa-list-alt"></i> <span id="cate_name_{{$child->id}}">{{$child->category_name}}</span>
                                                </div>
                                                <div class="li-right">
                                                    @if ($child->user->id == Auth::user()->id)
                                                        <button type="button"
                                                            onclick="mwOpen('mw_editCategory', 'mw_editCategory', {id:{{$child->id}}});"
                                                            class="btn btn-secondary"
                                                            style="padding:2px 10px"
                                                            @if ($child->user->id != Auth::user()->id)
                                                                disabled
                                                            @endif
                                                        ><i class="far fa-edit"></i></button>
                                                        <button type="button"
                                                            onclick="mwOpen('mw_deleteCategory', 'mw_deleteCategory', {id:{{$child->id}}});"
                                                            class="btn btn-danger"
                                                            style="padding:2px 10px; margin-left:0.8em"
                                                            @if ($child->user->id != Auth::user()->id)
                                                                disabled
                                                            @endif
                                                        ><i class="fas fa-trash"></i></button>
                                                    @endif
                                                </div>
                                                <br clear="both">
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item">カテゴリはありません。</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 追加modal -->
<div id="mw_addCategory" class="modal-overlay">
    <div class="modal-inner">
    <p>○カテゴリ追加</p>
    <form action="/scrap/categories/add" method="post">
    {{csrf_field()}}
    <input id="add_parent_id" name="parent_category_id" type="hidden" value="">
    <table class="table table-bordered table-striped"><tbody>
    <tr>
        <th>カテゴリ名</th>
    </tr><tr>
        <td class="form-group">
            <input type="text" id="add_category_name" name="category_name" value="" class="form-control">
        </td>
    </tr>
    </tbody></table>
    <br>
    <p style="width:100%;text-align:center">
        <button type="submit" class="btn btn-primary" onclick="return checkAddInput();">　O K　</button>
        <button type="button" class="btn btn-secondary" onclick="mwClose('mw_addCategory')">Cancel</button>
    </p>
    </form>
    </div>
</div>

<script>
mw_activate_funcs['mw_addCategory'] = function(params) {
    $('#add_category_name').val('');
    $('#add_parent_id').val(params.id);
};

function checkAddInput() {
    if ($('#add_category_name').val()) {
        return true;
    } else {
        toastr['error']('カテゴリ名を入力してください。');
        return false;
    }
}
</script>


<!-- 編集modal -->
<div id="mw_editCategory" class="modal-overlay">
    <div class="modal-inner">
    <p>○カテゴリ編集</p>
    <form action="/scrap/categories/edit" method="post">
    {{csrf_field()}}
    <input id="edit_category_id" name="id" type="hidden" value="">
    <table class="table table-bordered table-striped"><tbody>
    <tr>
        <th>カテゴリ名</th>
    </tr><tr>
        <td class="form-group">
            <input type="text" id="edit_category_name" name="category_name" value="" class="form-control">
        </td>
    </tr>
    </tbody></table>
    <br>
    <p style="width:100%;text-align:center">
        <button type="submit" class="btn btn-primary" onclick="return checkEditInput();">　O K　</button>
        <button type="button" class="btn btn-secondary" onclick="mwClose('mw_editCategory')">Cancel</button>
    </p>
    </form>
    </div>
</div>

<script>
mw_activate_funcs['mw_editCategory'] = function(params) {
    $('#edit_category_name').val($('#cate_name_' + params.id).text());
    $('#edit_category_id').val(params.id);
};

function checkEditInput() {
    if ($('#edit_category_name').val()) {
        return true;
    } else {
        toastr['error']('カテゴリ名を入力してください。');
        return false;
    }
}
</script>


<!-- 削除modal -->
<div id="mw_deleteCategory" class="modal-overlay">
    <div class="modal-inner">
    <p>○このカテゴリを削除します。よろしいですか？</p>
    <p><b>※</b>このカテゴリ以下に投稿が存在する場合削除できません。</p>
    <p>カテゴリ名：<span id="delete_category_name"></span></p>
    <form action="/scrap/categories/delete" method="post">
    {{csrf_field()}}
    <input id="delete_category_id" name="id" type="hidden" value="">
    <br>
    <p style="width:100%;text-align:center">
        <button class="btn btn-primary" type="submit">　O K　</button>
        <button type="button" class="btn btn-secondary" onclick="mwClose('mw_deleteCategory')">Cancel</button>
    </p>
    </form>
    </div>
</div>
<script>
mw_activate_funcs['mw_deleteCategory'] = function(params) {
    $('#delete_category_name').text($('#cate_name_' + params.id).text());
    $('#delete_category_id').val(params.id);
};
</script>
@endsection
