@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
@endsection

@section ('content')

<div class="table-responsive">
<table id="audits" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Username</th>
            <th>Page</th>
            <th>Status</th>
            <th>Before</th>
            <th>After</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($audits as $audit)
            <tr>
                <td>{{ $audit->username }}</td>
                <td>{{ $audit->page }}</td>
                <td>{{ $audit->status }}</td>
                <td>@if (is_array(unserialize($audit->action_before)))
                    @foreach (unserialize($audit->action_before) as $before)
                    <?php $parsed = (explode('=',$before)); ?>
                        @if ($parsed[0] == 'status')
                            {{  'status='.Status()->get($parsed[1] ) }}<br>
                        @else
                            {{  $before }}<br>
                        @endif
                    @endforeach
                    @endif
                </td>
                <td>@if (is_array(unserialize($audit->action_after)))
                    @foreach (unserialize($audit->action_after) as $after)
                    <?php $parsed = (explode('=',$after)); ?>
                        @if ($parsed[0] == 'status')
                            {{  'status='.Status()->get($parsed[1] ) }}<br>
                        @else
                            {{  $after }}<br>
                        @endif
                    @endforeach
                    @endif
                </td>
                <td style="width: 100px">{{$audit->created_at->format('m-d-Y')}}<br>{{$audit->created_at->format('g:H:s A')}}</td>
            </tr>
        @endforeach
    </tbody>

</table>
</div>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
        
    $(document).ready( function() {
        var table = $('#audits').DataTable({
            "deferRender": true,
   
        });
    
    })    
</script>
@endsection