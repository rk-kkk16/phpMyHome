@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">ダッシュボード</div>

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


                    <dash-kaimono-list></dash-kaimono-list>

                    <br>

                    <dash-goodpost></dash-goodpost>

                    <br>

                    <dash-imagepost></dash-imagepost>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
