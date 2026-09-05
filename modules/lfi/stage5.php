<?php
// 第 5 关：伪协议绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/lfi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $page = $_GET['page'] ?? 'home'; $output = ''; $passed = false;
if ($level === 'impossible') { $wl = ['home','about']; $output = in_array($page,$wl) ? '加载'.$page : '拒绝'; }
else { $output = @file_get_contents($page) ?: '不存在'; if (stripos($output,'<?php') !== false || stripos($output,'<?') !== false) $passed = true; if (!$passed && strlen($output) > 100) $passed = true; }
if ($passed) pass_stage('lfi',5,'page='.$page);

lfi_head(5, '伪协议绕过', 'php://filter读源码，php://input执行代码');
?>
<form method="get" class="lab"><div class="row"><label>伪协议</label><input type="text" name="page" value="<?= h($page) ?>" style="width:340px"></div><div class="row"><input type="submit" value="包含"></div></form>
<?php if ($output): ?><div class="result"><h3>内容：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) lfi_pass(true); ?>
<?php
lfi_tail(['hint' => '用php://filter读源码', 'full' => 'page=php://filter/convert.base64-encode/resource=/workspace/php-range/config.php'], [
    '原理' => 'LFI第5关：php://filter/read源码',
    '漏洞代码' => '<pre>include $page; // 允许伪协议</pre>',
    '攻击演示' => 'payload: page=php://filter/convert.base64-encode/resource=config.php',
    'payload详解' => 'page=php://filter/convert.base64-encode/resource=config.php<br>提示：php://filter读源码',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '禁用伪协议+白名单',
    '知识卡片' => 'LFI·伪协议'
], __FILE__);
