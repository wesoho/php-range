<?php
// 第 1 关：无过滤重定向
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/redirect_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $url = $_GET['url'] ?? ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $wl = ['home','about']; if (in_array($url, $wl)) { header('Location: /'.$url); } else { echo '拒绝'; } }
    else { header('Location: '.$url); $passed = true; }
}
if ($passed) pass_stage('redirect',1,'url='.$url);
redirect_head(1, '无过滤重定向', 'header Location直接拼接用户输入');
?>
<form method="get" class="lab"><div class="row"><label>跳转URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="跳转"></div></form>
<?php if ($passed) redirect_pass(true); ?>
<?php
redirect_tail(['hint' => '输入恶意URL', 'full' => 'url=https://evil.com'], [
    '原理' => '重定向第1关：header拼接URL',
    '漏洞代码' => '<pre>header("Location: ".$url);</pre>',
    '攻击演示' => 'payload: url=https://evil.com',
    'payload详解' => 'url=https://evil.com<br>恶意URL',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '白名单URL+相对路径检查',
    '知识卡片' => '重定向·无过滤'
], __FILE__);
