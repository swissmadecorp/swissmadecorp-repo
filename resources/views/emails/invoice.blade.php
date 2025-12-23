<html>
<head></head>
  <body>
    <p>Dear {{ $company }},</p>
    <p>We're glad you found what you were looking for! Details for your invoice 
    #{{$order_id}} is attached to this email in PDF format.</p>
    <p>If you are having troubles viewing your invoice, please notify us and we will send
      you a different format.</p>
    
    <p>Thank you,</p>

  <?php
        if ($purchasedFrom==0) {
            ?>
            <img src="https://www.swissmadecorp.com/images/logo.jpg" alt="" style="width: 140px" /><br>
            15 W 47th Street<br>
            Ste # 503<br>
            New York, NY 10036<br>
            Tel: 212-840-8463<br>
            info@swissmadecorp.com<br>
            </div>
  <?php } else {
            ?>
            <br>
            <b><i>SIGNATURE TIME</i></b><br>
            15 W 47th Street<br>
            Ste # 503<br>
            New York, NY 10036<br>
            Tel: 212-840-8463<br>
            signtimeny@gmail.com<br>
  <?php }

    ?>
  </body>
</html>