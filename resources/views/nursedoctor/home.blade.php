@extends('templates.layout')

@section('content')
<h1>Nurse / Doctor Dashboard</h1>
<p>Welcome, {{ auth()->user()->name }}!</p>
@endsection
