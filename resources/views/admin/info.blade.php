@if (count($errors))
<div class="pt-2 placeholders" style="clear:both">
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{$error}}</li>
                <li>Hello</li>
            @endforeach
        </ul>
    </div>
</div>
@endif