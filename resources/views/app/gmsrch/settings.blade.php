@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    サーチ設定
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
                        <div style="float:left">{{$settings->links()}}</div>
                        <a href="/gmsrch/settings/add?pre_page={{$settings->currentPage()}}"><button type="button" class="btn btn-primary" style="float:right">＋</button></a>
                        <br clear="both">
                    </div>
                    <div>
                        登録数：{{$settings->total()}}
                    </div>
                    <hr>

                    @forelse ($settings as $setting)
                        <div class="card">
                            <div class="card-header">
                                {{$setting->subject}}
                            </div>

                            <div class="card-body">
                                <table class="table table-bordered table-striped"><tbody>
                                    
                                </table>

                                <br><br>
                                <table><tbody>
                                    <tr>
                                        <th>登録：</th>
                                        <td>{{date('Y/m/d H:i', strtotime($setting->created_at))}}</td>
                                    </tr>
                                    <tr>
                                        <th>実施：</th>
                                        <td>{{date('Y/m/d H:i', strtotime($setting->last_crawled))}}</td>
                                    </tr>
                                    <tr>
                                        <th>次回：</th>
                                        <td>{{date('Y/m/d H:i', strtotime($setting->next_crawl))}}</td>
                                    </tr>
                                </tbody></table>
                            </div>
                        </div>
                        <br>
                    @empty
                        <p>投稿はありません。</p>
                    @endforelse

                    <hr>
                    <div>{{$imageposts->appends($params)->links()}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
