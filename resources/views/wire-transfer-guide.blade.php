@extends('layouts.default')

@section ('header')
<link href="fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<h2>Wire Transfer Guide</h2>
<hr>

<div class="content wire-transfer">
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            Wire transfer is a method of electronic funds transfer where money is sent from one bank 
            account to another. The process of a wire transfer typically involves the following steps:
            <ol>
                <li>
                    Initiation: The sender of the wire transfer initiates the process by providing their 
                    bank with the necessary information to transfer the funds, including the recipient's 
                    name, account number, and bank routing number.
                </li>
                <li>
                    Verification: The bank verifies the information provided by the sender to ensure that 
                    the transfer can be processed correctly. The bank may also verify the identity of the 
                    sender and may require additional documentation or information.
                </li>
                <li>
                    Processing: Once the information is verified, the bank processes the wire transfer. 
                    This involves debiting the sender's account and crediting the recipient's account with 
                    the transferred funds. The transfer of funds typically takes one to three business days 
                    to complete.
                </li>
                <li>
                    Notification: Once the wire transfer has been completed, the sender and recipient are 
                    notified of the transaction. The sender may receive a confirmation receipt, while the 
                    recipient will see the transferred funds in their account.
                </li>
            </ol>

            <p>
                Wire transfer is an electronic payment method that enables you to transfer funds from one 
                bank account to another. This payment method is widely used for international transactions or 
                for larger transactions where other payment methods may not be feasible. Wire transfer is also a 
                popular payment method for businesses, as it enables faster and more secure transactions.
            </p>
            <p>
                When it comes to wire transfer, you can trust that your transaction will be processed securely 
                and efficiently. We use state-of-the-art encryption and security protocols to protect your 
                financial information and prevent unauthorized access. Our team of experienced professionals 
                ensures that each wire transfer is processed accurately and quickly, minimizing the risk of 
                errors or delays.
            </p>
            <p>
                At our company, we are committed to providing you with a safe and secure payment experience.
                We understand the importance of protecting your financial information and ensuring the 
                confidentiality of your transactions. If you have any questions or concerns about wire transfer or 
                any of our other services, please do not hesitate to contact us.
            </p>
        </div>
    </div>
</div>

@endsection

@section ('footer')
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="{{ asset('/public/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/public/js/parsley.js') }}"></script>
@endsection
