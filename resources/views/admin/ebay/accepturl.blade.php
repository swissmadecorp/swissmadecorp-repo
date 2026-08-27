@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')
    @if ($success)
        <div class="alert alert-success">{{ $success }}</div>
    @else
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <p>You can now close this window.</p>

    <script>
        if (window.opener) {
            window.opener.postMessage(
                {
                    type: 'EBAY_OAUTH_COMPLETE',
                    success: @json((bool) $success),
                    error: @json($error),
                },
                window.location.origin
            );

            @if ($success)
                window.close();
            @endif
        }
    </script>
@endsection
