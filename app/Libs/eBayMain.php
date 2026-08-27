<?php

namespace App\Libs;

use App\Models\EbaySettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class eBayMain
{
	private $requestToken;

	public function getToken() {
        $token_ID = '';
        //if ()
        //customer_id: 65225528 username: mrdiamondusa password mrdiamondusa123*

        $ebaySettings = EbaySettings::first();

        if ($ebaySettings->token) {
            $token_ID = $ebaySettings->token;
        } else {

            $txt = '';
            $fh = fopen(base_path().'/resources/views/admin/ebay/config/my.token','r');
            while ($line = fgets($fh)) {
              $txt .= $line;
            }
            fclose($fh);
            $token_ID = $txt;

        }

		return $token_ID;
	}

    /**
     * Return an OAuth user access token for eBay REST APIs.
     *
     * A configured access token is useful for short-lived testing. In normal
     * operation, configure a refresh token so the access token can be renewed
     * automatically before each Media API call.
     */
    public function getOAuthUserToken(): string
    {
        $accessToken = trim((string) config('ebay.oauth_user_token'));

        if ($accessToken !== '') {
            return $accessToken;
        }

        $ebaySettings = EbaySettings::first();
        $storedAccessToken = trim((string) ($ebaySettings?->oauth_access_token ?? ''));
        $storedAccessTokenExpiresAt = $ebaySettings?->oauth_access_token_expires_at;

        if ($storedAccessToken !== '' && $storedAccessTokenExpiresAt?->isAfter(now()->addMinute())) {
            return $storedAccessToken;
        }

        $refreshToken = trim((string) config('ebay.oauth_refresh_token'))
            ?: trim((string) ($ebaySettings?->oauth_refresh_token ?? ''));

        if ($refreshToken === '') {
            throw new RuntimeException(
                'An eBay OAuth user token is required. Configure EBAY_OAUTH_REFRESH_TOKEN '
                .'with the sell.inventory scope (or EBAY_OAUTH_USER_TOKEN for short-lived testing).'
            );
        }

        $cacheKey = $this->oauthTokenCacheKey();
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $tokenUrl = config('ebay.flag_production')
            ? 'https://api.ebay.com/identity/v1/oauth2/token'
            : 'https://api.sandbox.ebay.com/identity/v1/oauth2/token';

        $response = Http::asForm()
            ->withBasicAuth(config('ebay.api_app_name'), config('ebay.api_cert_name'))
            ->timeout(30)
            ->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'scope' => 'https://api.ebay.com/oauth/api_scope/sell.inventory',
            ]);

        $token = $response->json('access_token');

        if (! $response->successful() || ! is_string($token) || $token === '') {
            $message = $response->json('error_description')
                ?: $response->json('error')
                ?: 'Unable to refresh the eBay OAuth user token.';

            throw new RuntimeException($message);
        }

        $ttl = max(60, ((int) $response->json('expires_in', 7200)) - 300);
        Cache::put($cacheKey, $token, now()->addSeconds($ttl));

        if ($ebaySettings) {
            $ebaySettings->update([
                'oauth_access_token' => $token,
                'oauth_access_token_expires_at' => now()->addSeconds((int) $response->json('expires_in', 7200)),
            ]);
        }

        return $token;
    }

    public function getOAuthAuthorizationUrl(string $state): string
    {
        $authorizationUrl = config('ebay.flag_production')
            ? 'https://auth.ebay.com/oauth2/authorize'
            : 'https://auth.sandbox.ebay.com/oauth2/authorize';

        return $authorizationUrl.'?'.http_build_query([
            'client_id' => config('ebay.api_app_name'),
            'redirect_uri' => config('ebay.runame'),
            'response_type' => 'code',
            'scope' => 'https://api.ebay.com/oauth/api_scope/sell.inventory',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function exchangeOAuthAuthorizationCode(string $authorizationCode): void
    {
        $tokenUrl = config('ebay.flag_production')
            ? 'https://api.ebay.com/identity/v1/oauth2/token'
            : 'https://api.sandbox.ebay.com/identity/v1/oauth2/token';

        $response = Http::asForm()
            ->withBasicAuth(config('ebay.api_app_name'), config('ebay.api_cert_name'))
            ->timeout(30)
            ->post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => config('ebay.runame'),
            ]);

        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        if (! $response->successful() || ! is_string($accessToken) || ! is_string($refreshToken)) {
            $message = $response->json('error_description')
                ?: $response->json('error')
                ?: 'Unable to exchange the eBay authorization code.';

            throw new RuntimeException($message);
        }

        $ebaySettings = EbaySettings::first();

        if (! $ebaySettings) {
            throw new RuntimeException('The ebay_settings record does not exist.');
        }

        $accessTokenLifetime = (int) $response->json('expires_in', 7200);
        $refreshTokenLifetime = (int) $response->json('refresh_token_expires_in', 47304000);

        $ebaySettings->update([
            'oauth_access_token' => $accessToken,
            'oauth_refresh_token' => $refreshToken,
            'oauth_access_token_expires_at' => now()->addSeconds($accessTokenLifetime),
            'oauth_refresh_token_expires_at' => now()->addSeconds($refreshTokenLifetime),
        ]);

        Cache::put(
            $this->oauthTokenCacheKey(),
            $accessToken,
            now()->addSeconds(max(60, $accessTokenLifetime - 300))
        );
    }

    private function oauthTokenCacheKey(): string
    {
        $environment = config('ebay.flag_production') ? 'production' : 'sandbox';

        return 'ebay.oauth_user_token.'.sha1($environment.config('ebay.api_app_name'));
    }

	public function sendHeaders ($xmlRequest,$API_CALL_NAME,$get_response="", $version=1349) {

        //ini_set('max_execution_time', 300);

        $headers = array(
        'X-EBAY-API-SITEID:'.config('ebay.site_id'),
        'X-EBAY-API-CALL-NAME:'.$API_CALL_NAME,
        'X-EBAY-API-SESSION-CERTIFICATE: '.config('ebay.api_dev_name').";".config('ebay.api_app_name').";".config('ebay.api_cert_name'),
        'X-EBAY-API-RESPONSE-ENCODING:XML',
        'X-EBAY-API-REQUEST-ENCODING:XML',
        'X-EBAY-API-COMPATIBILITY-LEVEL:' . $version,
        'X-EBAY-API-DEV-NAME:' . config('ebay.api_dev_name'),
        'X-EBAY-API-APP-NAME:' . config('ebay.api_app_name'),
        'X-EBAY-API-CERT-NAME:' . config('ebay.api_cert_name'),
        'Content-Type: text/xml;charset=utf-8'
        );

        // initialize our curl session

        $session = curl_init('https://api.'.(config('ebay.flag_production')==true ? '' : 'sandbox.').'ebay.com/ws/api.dll');

        // set our curl options with the XML request
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);

        // execute the curl request
        $responseXML = curl_exec($session);
        \Log::info($responseXML);

        // close the curl session
        curl_close($session);

        $xml = simplexml_load_string($responseXML, "SimpleXMLElement", LIBXML_NOCDATA);

        $namespaces = $xml->getNamespaces(true);

        // Navigate using the namespace
        $ack = (string) $xml->children($namespaces[''])->Ack;
        $errors = $xml->Errors;

        if ($ack == "Success") {
            return $xml;
        } elseif ($errors) {
            // Extract all <Errors> elements


            // Loop through errors and filter by ErrorCode
            $ErrorMessage = "";
            foreach ($errors as $error) {
                $errorCode = (string) $error->ErrorCode;

                // Check if the error code matches 21919067
                if ($errorCode === '21919067') {
                    $errorParameter = '';
                    foreach ($error->ErrorParameters as $param) {
                        $paramID = (string) $param['ParamID'];
                        if ($paramID == 1)
                            $errorParameter = (string) $param->Value;
                    }
                    return ["ErrorCode"=> '21919067', "ItemId" => $errorParameter, "ErrorMessage"=>"Listing violates the Duplicate Listing policy. It looks like this listing is for an item you already have on eBay"];
                } elseif ($errorCode === "21919136" || $errorCode === "21919137") {
                    return ["ErrorCode"=> '21919136', "ItemId" => null, "ErrorMessage"=>"Photo is too small or the resolution for provided picture(s) does not meet eBay's Picture Policy requirements. Must at least be 500x500"];
                }
            }
        } else {
            return $ack;
        }

        if ($get_response != "XML") {
            return $responseXML;
        }
    }
}
