@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">写真アップロード 投稿編集</div>


                <div class="card-body">
                    <p class="crumb"><a href="/imagepost/?impeval={{$pre_impeval}}&tag={{$pre_tag}}&category_id={{$pre_category_id}}&page={{$pre_page}}">&lt; 写真共有</a>
                    <a href="/imagepost/?pre_impeval={{$pre_impeval}}&pre_tag={{$pre_tag}}&pre_category_id={{$pre_category_id}}=pre_page={{$pre_page}}">&lt; 表示</a>
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


                    <form method="post" action="/imagepost/{{$post->id}}/edit">
                    <input type="hidden" name="pre_page" value="{{$pre_page}}">
                    <input type="hidden" name="pre_tag" value="{{$pre_tag}}">
                    <input type="hidden" name="pre_category_id" value="{{$pre_category_id}}">
                    <input type="hidden" name="pre_impeval" value="{{$pre_impeval}}">
                    {{csrf_field()}}
                    <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th>写真(※変更不可)</th>
                    </tr><tr>
                        <td>
                            <div id="uploaded_img">
                                <img src="/imagepost/photo/{{$post->id}}?img_size=300" style="max-width:300px; max-height:300px">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>キャプション(1000文字以内)</th>
                    </tr><tr>
                        <td class="form-group">
                            <textarea name="title" class="form-control">{{old('title', $post->title)}}</textarea>
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
                            <textarea name="tagtext" class="form-control" style="height:4em">{{old('tagtext', $post->tagtext)}}</textarea>
                            @if ($errors->has('tagtext'))
                                <p class="bg-danger">{{$errors->first('tagtext')}}</p>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    </table>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">保　存</button>
                        <button type="button" class="btn btn-secondary" onclick="location.href='/imagepost/?pre_impeval={{$pre_impeval}}&pre_tag={{$pre_tag}}&pre_category_id={{$pre_category_id}}=pre_page={{$pre_page}}'">キャンセル</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
