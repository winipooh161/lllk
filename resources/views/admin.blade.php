@extends('layouts.admin')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('admin/module/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('admin/home')
        </div>
    </div>
</div>
@endsection
