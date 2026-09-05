<?php
// 第 3 关：协议限制绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $url = $_GET['url'] ?? ''; $output = ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $output = '安全模式'; }
    else {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? '';
        $allowed = ['http','https'];
        if (!in_array($scheme, $allowed)) { $output = '拒绝：仅允许http/https'; }
        else {
            $output = @file_get_contents($url) ?: '请求失败';
            if (strlen($output) > 0) $passed = true;
        }
    }
}
if ($passed) pass_stage('ssrf', 3, 'url=' . $url);

ssrf_head(3, '协议限制绕过', '仅允许http/https但可用file绕过');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="请求"></div></form>
<?php if ($output): ?><div class="result"><h3>响应：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) ssrf_pass(true); ?>
<?php
ssrf_tail(['hint' => '用http协议访问内网服务', 'full' => 'url=http://127.0.0.1:3000/index.php'], [
    '原理' => 'SSRF 第3关：仅检查协议白名单，未检查目标IP',
    '漏洞代码' => '<pre>if (!in_array($scheme, ["http","https"])) die();
echo file_get_contents($url);</pre>',
    '攻击演示' => '用http协议访问内网服务',
    'payload详解' => 'http://127.0.0.1:3000/ 访问内网Web服务',
    '工具实操' => '探测内网端口和服务',
    '通关检测' => '成功获取响应即通关',
    '防御修复' => '协议白名单+IP白名单+禁止内网',
    '知识卡片' => '仅限协议不限目标=无效防护'
], __FILE__);
