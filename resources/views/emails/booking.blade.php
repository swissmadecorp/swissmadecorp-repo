<html>
<head></head>
  <body>
    <h1>Hello {{ $contactname }}</h1>
    <p>You have scheduled an appointment with us to see <b>{{$wristwatch}} ({{$product_id}})</b>.</p>
    <p>On {{ $book_date }} at {{ $book_time }}</p>
    
    <p>Please let us know if anything changes or you won't be able to keep this appointment.</p>

    <p>Thank you,</p>
    <p>Swiss Made Corp.<br>www.swissmadecorp.com </p>
    <hr>
  </body>
</html>