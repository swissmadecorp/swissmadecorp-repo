@extends('layouts.admin-default')

@section ('content')

<div class="m_bottom_25">
{!! Form::open(
    array(
        'route' => 'import.upload', 
        'class' => 'form', 
        'novalidate' => 'novalidate', 
        'files' => true)) !!}

    <input type="file" name="csvfile">
    <button type="submit" class="btn btn-primary">Upload</button>

{!! Form::close() !!}
</div>

@if (Session::has('message'))
{!! Form::open(
    array(
        'route' => 'import.import', 
        'class' => 'form', 
        )) !!}
        
        <input type="hidden" value="{{ Session::get('filename') }}" name="filename">
        <button type="submit" class="btn btn-success">Import</button>
{!! Form::close() !!}

@endif
@endsection