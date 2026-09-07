<?php
// 第 1 关：无过滤SSRF
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $url = $_GET['url'] ?? ''; $output = ''; $passed = false;
if ($url) {
    if ($level === 'impossible') {
        $ip = @gethostbyname(parse_url($url, PHP_URL_HOST) ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)) $output = '拒绝：不允许内网';
        else $output = @file_get_contents($url) ?: '请求失败';
    } else {
        $output = @file_get_contents($url) ?: '请求失败';
        if (stripos($output,'root:') !== false || stripos($output,'[fonts]') !== false || stripos($output,'for 16-bit') !== false) $passed = true;
    }
}
if ($passed) pass_stage('ssrf', 1, 'url=' . $url);

ssrf_head(1, '无过滤SSRF', '服务器请求任意URL，可读内网/本地文件');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="请求"></div></form>
<?php if ($output): ?><div class="result"><h3>响应：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) ssrf_pass(true); ?>
<?php
ssrf_tail(['hint' => '用 file:// 协议读本地文件', 'full' => 'url=file:///etc/passwd　或　url=file:///C:/Windows/win.ini'], [
    '原理' => 'SSRF 第1关：file_get_contents($url) 无任何过滤，支持 file:// http:// ftp:// 等协议',
    '漏洞代码' => '<pre>$url = $_GET["url"];
echo file_get_contents($url);</pre>',
    '攻击演示' => '输入 file:///etc/passwd 读取系统文件',
    'payload详解' => 'file:///etc/passwd 利用 file 协议读取本地文件',
    '工具实操' => 'curl "http://靶场/stage1.php?url=file:///etc/passwd"',
    '通关检测' => '响应中包含 root: 即通关',
    '防御修复' => '白名单域名+禁止内网IP+限制协议',
    '知识卡片' => 'SSRF 可读文件/访问内网/探测端口'
], __FILE__);
