@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')
@if ($announcement)
{{  Form::model($announcement, array('route' => array('announcements.update', $announcement->id), 'method' => 'PATCH', 'id' => 'announcement-form')) }} 
@else
{{  Form::open(array('route'=>array('announcements.store'), 'id' => 'announcements-form')) }}
@endif
    <input type="hidden" value="1" name="announcement_id"/>

    <div class="form-group row">
        <label for="title-input" class="col-3 col-form-label">Title *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('title')) ? old('title') : (!empty($announcement->title) ? $announcement->title : '') ?>" type="text" placeholder="Enter title" name="title" id="title-input" required>
        </div>
    </div>  

    <div class="form-group row">
        <label for="content-input" class="col-3 col-form-label">Content *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('content')) ? old('content') : (!empty($announcement->content) ? $announcement->content : '') ?>" type="text" placeholder="Enter content" name="content" id="content-input" required>
        </div>
    </div>  

    <button type="submit" class="btn btn-primary">Submit</button>

    @include('admin.errors')

    {{  Form::close() }}  

@endsection
