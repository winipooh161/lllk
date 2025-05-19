@extends('layouts.admin')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            @include('admin/module/user_briefs')
            
        </div>
    </div>
</div>
@endsection
