@extends('templates.admin')

@section('CSSown')
<style>
.dev-container {
    min-height: 80vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.dev-box {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.dev-box h1 {
    font-size: 28px;
    margin-bottom: 10px;
}

.dev-box p {
    color: #555;
}
</style>
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Analytics</h1>
        <p>In Development...</p>
    </div>
</div>

<div class="dev-container">
    <div class="dev-box">
        <h1>🚧 Still in Development</h1>
        <p>This module is currently under development. Please check back later.</p>
    </div>
</div>

@endsection