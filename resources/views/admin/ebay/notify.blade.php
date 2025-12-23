<?php
##########################################################
### Marketplace Account Deletion Notifications Handler ###
### By Swappart 2021                                   ###
##########################################################
 
### Sign into eBay Developers Program and find your Client ID and Client Secret under application access keys.
### Your endpoint and verificationToken are values you specify when subscribing to Marketplace Account Deletion Notifications in your developer account.
### The endpoint URL must not contain the word eBay.
### The verification token has to be between 32 and 80 characters, and allowed characters include alphanumeric characters, underscore (_),  and hyphen (-).
### No other characters are allowed. Example: 654321abcdef654321abcefd123456fe;
 
$client_id =  "EdwardBa-dbe1-4a78-8848-5433a7bddb11";  //Also known as App ID.
$client_secret =  "4d04a43f-fd2d-4545-b76a-d3d99af075a7"; //Also known as Cert ID.
$verificationToken = "whatthehellisthevalidationcodeIdontunderstand212";
 
#####################################
### This part validates endpoint. ###
#####################################
if(isset($_GET['challenge_code'])){
$endpoint = 'https://swissmadecorp.com/ebay/notify';
$challengeCode = $_GET['challenge_code'];
header('Content-Type: application/json'); $d=$challengeCode.$verificationToken.$endpoint; $hd=array("challengeResponse"=>hash("sha256", $d));
 
echo(json_encode($hd));
}
 
#######################################
### This part handles notfications. ###
#######################################
if(isset($_SERVER['HTTP_X_EBAY_SIGNATURE'])){
 
$json = file_get_contents('php://input');
$message = json_decode($json, true);
if (!$message) {
    throw new Exception('Invalid message');
}
 
if (empty($_SERVER['HTTP_X_EBAY_SIGNATURE'])) {
    throw new Exception('No signature passed');
}
 
$signature = json_decode(base64_decode($_SERVER['HTTP_X_EBAY_SIGNATURE']), true) ?: [];
if (empty($signature['kid'])) {
    throw new Exception('Signature not decoded');
}
 
$token = retrieveToken($client_id, $client_secret);
 
$ch = curl_init();
$fp = fopen("curlLog.txt", "w") or die("Unable to open file!");
curl_setopt($ch, CURLOPT_URL, "https://api.ebay.com/commerce/notification/v1/public_key/" . $signature['kid']);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type = application/json','Accept: application/json', 'Authorization:bearer ' . $token));
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
 
 
$response = curl_exec($ch);
 
 
 
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
 
if ($curl_errno > 0) {
    
   fwrite($fp, "cURL Error ($curl_errno): $curl_error\n");
        } else {
            fwrite($fp, "Data received: $response\n");
            
        }
 
$publicKey = json_decode($response, true);
 
curl_close($ch);
fclose($fp);
 
if (empty($publicKey['key'])) {
    throw new Exception(
        'getPublicKey response: ' . json_encode($publicKey) . ' for signature ' . $signature['kid']
    );
}
 
if ($publicKey['algorithm'] !== 'ECDSA' || $publicKey['digest'] !== 'SHA1') {
    throw new Exception('Unsupported encryption algorithm/digest');
}
 
if (preg_match('/^-----BEGIN PUBLIC KEY-----(.+)-----END PUBLIC KEY-----$/', $publicKey['key'], $matches)) {
    $key = "-----BEGIN PUBLIC KEY-----\n"
        . implode("\n", str_split($matches[1], 64))
        . "\n-----END PUBLIC KEY-----";
} else {
    throw new Exception('Invalid key');
}
 
 
$verificationResult = openssl_verify(
    json_encode($message),
    base64_decode($signature['signature']),
    $key,
    OPENSSL_ALGO_SHA1
);
 
 
 
if ($verificationResult === 1) {
    echo 'OK';
} else {
$myfile = fopen("verification-error.txt", "w") or die("Unable to open file!");
$txt = "Verification Failed!";
fwrite($myfile, $txt);
fclose($myfile);
    throw new Exception('Verification failure', 412);
}
} 
 
 
 
###################################################################################################################
### This part returns stored OAuth token, or new token if stored one is expired. Tokens only valid for 2 hours. ###
###################################################################################################################
function retrieveToken($client_id, $client_secret) {
 
$date = new DateTime();
 $date->getTimestamp();
 $auth = null;
 if(file_exists('auth.ini')){
  $auth = parse_ini_file('auth.ini', true);   
if(!isset($auth['time']) ||  !isset($auth['token'])){
    //ini file exists but doesn't contain token. We'll fetch one and update the file.
    $token = getNewToken($client_id, $client_secret);
}else{
   $s = $auth['time'];
   $t= new DateTime("@$s");
   
    if($date->getTimestamp() > $t->add(new DateInterval('PT7170S'))->getTimestamp()){
        //Token expired. We'll fetch a new one.
        $token = getNewToken($client_id, $client_secret);
    }else{
        //Stored token still good! Using it"
        $token = $auth['token'];
        
    }
}
 
 }else{
     //ini file doesn't exist yet. We'll fetch a token and create the file.
     $token = getNewToken($client_id, $client_secret); 
 }
 
 return $token;
 
}
 
 
 
 
##########################################
### This part fetches new OAuth token. ###
##########################################
function getNewToken($client_id, $client_secret) {
 
$ch = curl_init();
 
curl_setopt($ch, CURLOPT_URL, 'https://api.ebay.com/identity/v1/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=https://api.ebay.com/oauth/api_scope");
 
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
$headers[] = 'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);
$tk = json_decode($result, true);
$date = new DateTime();
$time = $date->getTimestamp();
 
 
// Update values
$config['time'] = $time;
$config['token'] = $tk['access_token'];
 
if(!file_exists('auth.ini')){
$inifile = fopen("auth.ini", "w") or die("Unable to open file!");
fclose($inifile);
// Write ini file values
write_ini_file('auth.ini', $config);
}else{
// write ini file
write_ini_file('auth.ini', $config);
}
    
  return $tk['access_token']; 
}
 
 
 
###########################################################
### This part writes OAuth token to file for later use. ###
###########################################################
function write_ini_file($file, $array = []) {
        // check first argument is string
        if (!is_string($file)) {
            throw new \InvalidArgumentException('Function argument 1 must be a string.');
        }
 
        // check second argument is array
        if (!is_array($array)) {
            throw new \InvalidArgumentException('Function argument 2 must be an array.');
        }
 
        // process array
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }
 
        // open file pointer, init flock options
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;
 
        if (!$fp) {
            return false;
        }
 
        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);
 
        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }
 
        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);
 
        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);
 
        return true;
    }
?>