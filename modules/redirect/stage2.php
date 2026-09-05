<?php
// 第 2 关：白名单绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/redirect_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $url = $_GET['url'] ?? ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $wl = ['home','about']; if (in_array($url, $wl)) { header('Location: /'.$url); exit; } else echo '拒绝'; }
    else { $host = parse_url($url, PHP_URL_HOST) ?? ''; if (strpos($host, 'example.com') !== false || empty($host)) { header('Location: '.$url); $passed = true; } else { echo '拒绝：非白名单域名'; } }
}
if ($passed) pass_stage('redirect',2,'url='.$url);
redirect_head(2, '白名单绕过', '检查域名但可用@绕过');
?>
<form method="get" class="lab"><div class="row"><label>跳转URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="跳转"></div></form>
<?php if ($passed) redirect_pass(true); ?>
<?php
redirect_tail(['hint' => '用@绕过域名检查', 'full' => 'url=https://evil.com@example.com'], [
    '原理' => '重定向第2关：检查域名但@绕过',
    '漏洞代码' => '<pre>检查host含example.com</pre>',
    '攻击演示' => 'payload: url=https://evil.com@example.com',
    'payload详解' => 'url=https://evil.com@example.com<br>@绕过',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '严格白名单+不解析@',
    '知识卡片' => '重定向·白名单绕过'
], __FILE__);
