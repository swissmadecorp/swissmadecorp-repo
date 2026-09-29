<div style="text-align:left">
    <p>A watch matching the following customer request is now available:</p>
    @foreach($reminders as $reminder)
        <div style="margin-top:16px">
            <a href="{{ route('reminders', ['reminder' => $reminder->id]) }}" style="color:#2563eb;text-decoration:underline;font-weight:600">{{ $reminder->customer_name ?: ($reminder->assigned_to ?: 'View customer details') }}</a>
            <p>Is waiting for {{ $reminder->criteria ?: 'this watch' }}.</p>
            @if($reminder->customer_phone)<p>{{ $reminder->customer_phone }}</p>@endif
            @if($reminder->customer_email)<p>{{ $reminder->customer_email }}</p>@endif
        </div>
    @endforeach
    <p style="margin-top:16px;font-size:14px">Click a customer’s name to open their reminder details.</p>
</div>
