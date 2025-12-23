<html>
<head></head>
  <body>
      {{ $order_id }} has been flagged as <b style="color: green;">{{ $status }}</b>.
      
      <p>It's {!! ($status == 'Insured') ? "<span style='color: green'>SAFE</span>" : "<span style='color: red'>UNSAVE</span>" !!} to ship the package.</p>
    <hr>
  </body>
</html>