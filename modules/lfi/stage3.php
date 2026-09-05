<?php
// 第 3 关：双写绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/lfi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $page = $_GET['page'] ?? 'home'; $output = ''; $passed = false;
if ($level === 'impossible') { $page = basename($page); $output = '安全'; }
else { $page = str_replace('../','',$page); $output = @file_get_contents($page) ?: '不存在'; if (stripos($output,'root:') !== false) $passed = true; }
if ($passed) pass_stage('lfi',3,'page='.$page);

lfi_head(3, '双写绕过', 'str_replace过滤../但可用..././绕过');
?>
<form method="get" class="lab"><div class="row"><label>页面</label><input type="text" name="page" value="<?= h($page) ?>" style="width:340px"></div><div class="row"><input type="submit" value="包含"></div></form>
<?php if ($output): ?><div class="result"><h3>内容：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) lfi_pass(true); ?>
<?php
lfi_tail(['hint' => '双写..././绕过str_replace', 'full' => 'page=..././..././..././etc/passwd'], [
    '原理' => 'LFI第3关：str_replace单次替换',
    '漏洞代码' => '<pre>str_replace("../","",$page);</pre>',
    '攻击演示' => 'payload: page=..././..././etc/passwd',
    'payload详解' => 'page=..././..././etc/passwd<br>提示：双写绕过',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '循环替换或用realpath',
    '知识卡片' => 'LFI·双写绕过'
], __FILE__);
