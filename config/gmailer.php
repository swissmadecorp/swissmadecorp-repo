<?php

return [

	'refresh_token' => env('REFRESH_TOKEN'),
	'client_id' => env('GOOGLE_CLIENT_ID'),
	'client_secret' => env('GOOGLE_CLIENT_SECRET'),
	'redirect_url' => env('GOOGLE_REDIRECT_URI', '/'),

	'mail_from' => env('GOOGLE_MAIL_FROM_ADDRESS'),

	'access_type' => 'offline',

];
