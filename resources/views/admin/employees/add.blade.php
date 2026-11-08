@extends('layouts.admin-app')
@section('title', 'Add employee')
@section('content')
<div class="admin-heading"><div><h1>Add an employee</h1></div><a class="btn btn-primary" href="{{ route('employees.list', ['add' => 1]) }}">Open employee form</a></div>
@endsection
