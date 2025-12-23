<html>
<head></head>
  <body>
    <h1>I would like to sell my watch.</h1>
    <p>Contact Name: {{ $contacts['name'] }}</p>
    <p>Email: {{ $contacts['email'] }}</p>
    <p>Phone: {{ $contacts['phone'] }}</p>
    <p>Brand Name: {{ $contacts['brand'] }} {{ $contacts['model'] }}</p>

    <p>Sell Amount: {{ $contacts['amount'] }}</p>
    <p>Watch Age: {{ $contacts['age'] }}</p>

    <p>About Watch: <br>{!! $selections !!}</p>
      
    @if (!empty($contacts['comment']))
      <p><strong>{{ $contacts['comment'] }}</strong></p>
    @endif
    {!! $image !!}
    <hr>
  </body>
</html>