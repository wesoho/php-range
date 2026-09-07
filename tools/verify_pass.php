<?php
// 通关语义验证 v2：自登录 + 智能参数匹配 + 说明性提示自动跳过
$base = 'http://127.0.0.1:8808';
$jar = sys_get_temp_dir() . '/vp-cookie-' . getmypid() . '.txt';

function http_get($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_COOKIEJAR => $GLOBALS['jar'],
        CURLOPT_COOKIEFILE => $GLOBALS['jar'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    return $r === false ? '' : $r;
}

// 登录
http_get($base . '/login.php');
$ch = curl_init($base . '/login.php');
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => 'username=admin&password=admin123',
    CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_RETURNTRANSFER => true]);
curl_exec($ch); curl_close($ch);
http_get($base . '/settings.php'); // level=low 默认

// 切低难度
$ch = curl_init($base . '/settings.php');
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => 'level=low&hint=full',
    CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_RETURNTRANSFER => true]);
curl_exec($ch); curl_close($ch);

$paramMap = [
    'sqli' => ['id', 'user', 'username'],
    'xss' => ['x', 'name', 'content', 'msg', 'comment', 'q', 'search', 'input', 'keyword'],
    'cmdi' => ['ip', 'host', 'cmd', 'target'],
    'upload' => [],
    'csrf' => ['newpwd', 'pwd', 'password'],
    'ssrf' => ['url', 'u', 'target'],
    'lfi' => ['page', 'file', 'path'],
    'xxe' => ['xml', 'data'],
    'deser' => ['data', 'cookie'],
    'logic' => ['uid', 'id', 'old', 'new', 'code', 'captcha', 'amount', 'key', 'role', 'action'],
    'ssti' => ['tpl', 'name', 'content'],
    'rce' => ['code', 'func'],
    'ldap' => ['input', 'user', 'username', 'uid', 'name'],
    'redirect' => ['url', 'next', 'to', 'redirect'],
    'crlf' => ['url', 'input', 'log', 'msg', 'email', 'val'],
    'session' => ['sid', 'session_id', 'id'],
    'infoleak' => ['dir', 'file', 'path'],
    'crypto' => ['input', 'token', 'password', 'data'],
];

// 说明性提示关键词（人工步骤，非机器 payload）
$manualWords = ['上传','访问','观察','构造','多线程','快速','检查','分析','删除','传入','长时间','不传','查看','触发','使用','Burp','curl','写在前','URL 末尾','表单已','Token'];

function clean_payload($p) {
    // 只去掉全角括号内的中文说明，保留英文括号（payload 的一部分）
    $p = preg_replace('/（[^）]*）/', '', $p);
    return trim($p);
}

$ok = []; $fail = []; $manual = [];
foreach ($paramMap as $mod => $params) {
    foreach (glob("modules/$mod/stage*.php") as $file) {
        $stage = basename($file, '.php');
        $src = file_get_contents($file);
        if (!preg_match("/'full'\s*=>\s*(?:'((?:[^'\\\\]|\\\\.)*)'|\"((?:[^\"\\\\]|\\\\.)*)\")/", $src, $m)) { $manual[] = "$mod/$stage 无full"; continue; }
        $payload = stripcslashes($m[1] !== '' ? $m[1] : $m[2]);
        // 多个备选 payload 用 全角 或 半角"或"分隔，取第一段
        $payload = preg_split('/　或　|\s或\s/', $payload)[0];
        $payload = clean_payload($payload);
        if ($payload === '') { $manual[] = "$mod/$stage 纯说明"; continue; }
        foreach ($manualWords as $w) {
            if (mb_strpos($payload, $w) !== false) { $manual[] = "$mod/$stage: $payload"; continue 2; }
        }
        $hit = false; $errTxt = '';
        $tries = [];
        // payload 自带 param= 前缀且在参数表中 → 直接用
        if (preg_match('/^([a-z_0-9]+)=(.+)$/s', $payload, $pm) && in_array($pm[1], $params, true)) {
            $tries[] = $pm[1] . '=' . $pm[2];
        } else {
            foreach ($params as $p) $tries[] = $p . '=' . $payload;
        }
        foreach ($tries as $q) {
            // 正确 URL 编码：payload 自带 param= 前缀时拆开，否则作为参数值
            if (preg_match('/^([a-z_0-9]+)=(.+)$/s', $q, $qm)) {
                $qs = http_build_query([$qm[1] => $qm[2]]);
            } else {
                $qs = $q;
            }
            $out = http_get("$base/modules/$mod/$stage.php?" . $qs);
            if (strpos($out, 'banner ok') !== false || strpos($out, 'alert-success') !== false) { $hit = true; break; }
            if (preg_match('/(SQL 错误[^<]{0,60}|Fatal error[^<]{0,50}|Parse error[^<]{0,50}|Uncaught[^<]{0,50})/', $out, $e)) $errTxt = html_entity_decode(strip_tags($e[0]));
        }
        if ($hit) $ok[] = "$mod/$stage";
        else $fail[] = [$mod . '/' . $stage, $payload, $errTxt ?: '未触发通关'];
    }
}

echo "===== PASS (" . count($ok) . ") =====\n" . implode("\n", $ok) . "\n";
echo "===== MANUAL/说明性 (" . count($manual) . ") =====\n" . implode("\n", $manual) . "\n";
echo "===== FAIL (" . count($fail) . ") =====\n";
foreach ($fail as $f) echo "[$f[0]] payload=$f[1] => $f[2]\n";
@unlink($jar);
