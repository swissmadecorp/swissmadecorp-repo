<html>
<head></head>
  <body>
    <h1>You have a new SwissMade inquiry.</h1>
    <p>Contact Name: {{ $fullname }}</p>
    <img src="https://swissmadecorp.com/images/thumbs/{{$image}}" alt="" width="150" height="150">
    <p>Product Name: {{ $product }}</p>
    <p>Product #: {{ $product_id }}</p>
    <p>Email: {{$email}} - Phone: {{ $phone }}</p>
    <p><strong>{{ $notes }}</strong></p>
    <hr>
  </body>
</html>