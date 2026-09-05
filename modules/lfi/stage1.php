<?php
// 第 1 关：本地文件包含
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/lfi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $page = $_GET['page'] ?? 'home'; $output = ''; $passed = false;
if ($level === 'impossible') { $wl = ['home','about']; $output = in_array($page,$wl) ? '加载'.$page : '拒绝'; }
else { $output = @file_get_contents($page) ?: '文件不存在'; if (stripos($output,'root:') !== false) $passed = true; }
if ($passed) pass_stage('lfi',1,'page='.$page);

lfi_head(1, '本地文件包含', 'include直接拼接用户输入');
?>
<form method="get" class="lab"><div class="row"><label>文件路径</label><input type="text" name="page" value="<?= h($page) ?>" style="width:340px"></div><div class="row"><input type="submit" value="包含"></div></form>
<?php if ($output): ?><div class="result"><h3>内容：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) lfi_pass(true); ?>
<?php
lfi_tail(['hint' => '直接读/etc/passwd', 'full' => 'page=/etc/passwd'], [
    '原理' => 'LFI第1关：include直接拼接用户输入',
    '漏洞代码' => '<pre>include $_GET["page"];</pre>',
    '攻击演示' => 'payload: page=/etc/passwd',
    'payload详解' => 'page=/etc/passwd<br>提示：直接读/etc/passwd',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '白名单+路径规范化',
    '知识卡片' => 'LFI·本地文件包含'
], __FILE__);
