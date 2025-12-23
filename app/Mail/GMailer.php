<?php

namespace App\Mail;

use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;

class GMailer
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function getGoogleToken() {
        $params = [
            'clientId' => config('gmailer.client_id'),
            'clientSecret' => config('gmailer.client_secret'),
            'redirectUri' => config('gmailer.redirect_url'),
            'accessType' => config('gmail.access_type')
        ];

        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];

        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl($options);
            header('Location: ' . $authUrl);
            exit;
        }

        $token = $provider->getAccessToken(
            'authorization_code',
            [
                'code' => $_GET['code']
            ]
        );

        return $token->getRefreshToken();
    }

    public function send() {

        $mail = new PHPMailer(true);

        // Comment the following lines of code till $mail->Port to send
        // mail using phpmail instead of smtp.

        $mail->isSMTP();

        //Enable SMTP debugging
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        $mail->SMTPDebug = SMTP::DEBUG_OFF;

        //Set the hostname of the mail server
        $mail->SMTPSecure = 'tls';
        $mail->Host='smtp.gmail.com';

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 587;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Set AuthType to use XOAUTH2
        $mail->AuthType = 'XOAUTH2';

        //Fill in authentication details here
        //Either the gmail account owner, or the user that gave consent
        $oauthUserEmail = config('gmailer.mail_from');

        $clientId = config('gmailer.client_id');
        $clientSecret = config('gmailer.client_secret');
            // 'redirectUri' => config('gmailer.redirect_url'),
            // 'accessType' => config('gmail.access_type')

        //Obtained by configuring and running get_oauth_token.php
        //after setting up an app in Google Developer Console.
        $refreshToken = config('gmailer.refresh_token');

        //Create a new OAuth2 provider instance
        $provider = new Google(
            [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]
        );

        //Pass the OAuth provider instance to PHPMailer
        $mail->setOAuth(
            new OAuth(
                [
                    'provider' => $provider,
                    'clientId' => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName' => $oauthUserEmail,
                ]
            )
        );

        $isArray = false;
        if (isset($this->event['filename'])) {
            // $attachments is an array with file paths of attachments
            if (is_array($this->event['filename']) && count($this->event['filename'])>0) {
                $isArray = true;
            }

            if ($isArray) {
                foreach($this->event['filename'] as $filePath){
                    $mail->addAttachment(public_path().'/uploads/'.$filePath);
                }
            } else {
                $mail->addAttachment(public_path().'/uploads/'.$this->event['filename']);
            }
        }

        // Recipients
        $mail->setFrom(config('gmailer.mail_from'), "Customer Support");

        if (isset($this->event['fullname']))
            $fullname=$this->event['fullname'];
        else $fullname = $this->event['to'];

        $mail->addAddress($this->event['to'], $fullname);
        if (isset($this->event["replyTo"]))
            $mail->addReplyTo($this->event["replyTo"], $fullname);
        else $mail->addReplyTo(config('gmailer.mail_from'), "Customer Support");

        $mail->Subject = $this->event['subject'];
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $html = view($this->event['template'], $this->event)->render();
        $mail->msgHTML($html);
        //$mail->AltBody = 'This is a plain-text message body';
        $mail->send();
    }
}