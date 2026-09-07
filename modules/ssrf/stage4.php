<?php
// 第 4 关：URL解析差异
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $url = $_GET['url'] ?? ''; $output = ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $output = '安全模式'; }
    else {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        if (strpos($host, '127.0.0.1') !== false || strpos($host, 'localhost') !== false) {
            $output = '拒绝：不允许内网';
        } else {
            $output = @file_get_contents($url) ?: '请求失败';
            if (stripos($output,'root:') !== false || stripos($output,'[fonts]') !== false || stripos($output,'for 16-bit') !== false || stripos($output, 'DOCTYPE') !== false) $passed = true;
        }
    }
}
if ($passed) pass_stage('ssrf', 4, 'url=' . $url);

ssrf_head(4, 'URL解析差异', '检查parse_url的host但可用@绕过');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="请求"></div></form>
<?php if ($output): ?><div class="result"><h3>响应：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) ssrf_pass(true); ?>
<?php
ssrf_tail(['hint' => '用file://协议绕过host检查', 'full' => 'url=file:///etc/passwd　或　url=file:///C:/Windows/win.ini'], [
    '原理' => 'SSRF 第4关：parse_url对file://协议返回空host，绕过检查',
    '漏洞代码' => '<pre>$host = parse_url($url, PHP_URL_HOST);
if (strpos($host, "127.0.0.1") !== false) die();</pre>',
    '攻击演示' => 'file://协议的host为空，不触发检查',
    'payload详解' => 'file:///etc/passwd 绕过host验证',
    '工具实操' => '利用URL解析差异绕过',
    '通关检测' => '响应包含root:或DOCTYPE即通关',
    '防御修复' => '统一用gethostbyname解析真实IP',
    '知识卡片' => 'parse_url与真实请求的解析差异是常见绕过点'
], __FILE__);
