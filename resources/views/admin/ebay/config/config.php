<?php 

	// define our header array for the Trading API call
	// notice different headers from shopping API and SITE_ID changes to SITEID
	DEFINE("SITEID",0);
	// production vs. sandbox flag - true=production
	DEFINE("FLAG_PRODUCTION",true);
	// eBay Trading API version to use
	DEFINE("API_COMPATIBILITY_LEVEL",863);
	DEFINE("USER_URL", 'server/php/files');
	DEFINE("HOSTINGPATH", 'swissmadecorp.com/');
	DEFINE("MULTIUSER",true);
	
	if (FLAG_PRODUCTION == false) {
		define ("SANDBOX", 'sandbox.');
		define ('API_DEV_NAME','7ed4da3b-b935-4ebf-aca0-8b400b60d14a');
		define ('API_APP_NAME','EdwardBa-605a-4289-a763-a9463d9698fc');
		define ('API_CERT_NAME','dded6470-ea66-4062-b53c-30bf403797a2');
		define ('RESPONSE_ENCODING','XML');
		define ('DB_SERVER','localhost');
		define ('DB_NAME','swissmade');
		define ('DB_USER','root');
		define ('DB_PW','');
	} else {
		define ('API_DEV_NAME','7ed4da3b-b935-4ebf-aca0-8b400b60d14a');
		define ('API_APP_NAME','EdwardBa-dbe1-4a78-8848-5433a7bddb11');
		define ('API_CERT_NAME','4d04a43f-fd2d-4545-b76a-d3d99af075a7');
		define ('RESPONSE_ENCODING','XML');
	}
	
	
?>