@extends('layouts.app')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module profile-page-container">
            @include('layouts/header')
            @include('module/profile_view')
        </div>
    </div>
</div>
@endsection
