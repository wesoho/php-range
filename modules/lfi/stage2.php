<?php
// 第 2 关：路径穿越
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/lfi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $page = $_GET['page'] ?? 'home'; $output = ''; $passed = false;
if ($level === 'impossible') { $page = basename($page); $output = '安全：'.$page; }
else { $file = APP_ROOT.'/pages/'.$page; $output = @file_get_contents($file) ?: @file_get_contents($page) ?: '不存在'; if (stripos($output,'root:') !== false) $passed = true; }
if ($passed) pass_stage('lfi',2,'page='.$page);

lfi_head(2, '路径穿越', 'include pages/.$page，用../穿越');
?>
<form method="get" class="lab"><div class="row"><label>页面</label><input type="text" name="page" value="<?= h($page) ?>" style="width:340px"></div><div class="row"><input type="submit" value="包含"></div></form>
<?php if ($output): ?><div class="result"><h3>内容：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) lfi_pass(true); ?>
<?php
lfi_tail(['hint' => '用../穿越目录前缀', 'full' => 'page=../../../etc/passwd'], [
    '原理' => 'LFI第2关：目录前缀+../穿越',
    '漏洞代码' => '<pre>include "pages/".$page;</pre>',
    '攻击演示' => 'payload: page=../../../etc/passwd',
    'payload详解' => 'page=../../../etc/passwd<br>提示：用../穿越',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => 'basename()+白名单',
    '知识卡片' => 'LFI·路径穿越'
], __FILE__);
