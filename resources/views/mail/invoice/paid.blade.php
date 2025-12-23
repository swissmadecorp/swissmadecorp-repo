@component('mail::message')
# Introduction

Your invoice has been paid!

@component('mail::button', ['url' => $url])
View Invoice - {{$order_id}}
@endcomponent

Thanks,<br>
Swiss Made Corp.
@endcomponent
