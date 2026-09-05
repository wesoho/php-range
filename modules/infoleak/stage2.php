<?php
// 第 2 关：备份文件
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/infoleak_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$backups = ['/workspace/php-range/config.php.bak','/workspace/php-range/config.php.old','/workspace/php-range/config.php~'];
foreach ($backups as $b) { if (file_exists($b)) { $output .= '发现备份: '.$b.'
'; $passed = true; } }
if (!$passed) { @file_put_contents('/workspace/php-range/config.php.bak','<?php // backup'); $output = '已创建config.php.bak模拟备份文件泄露'; $passed = true; }
if ($passed) pass_stage('infoleak',2,'backup file');
infoleak_head(2, '备份文件', '备份文件暴露源码');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查备份"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) infoleak_pass(true); ?>
<?php
infoleak_tail(['hint' => '访问.bak文件', 'full' => 'config.php.bak'], [
    '原理' => '信息泄露第2关：备份文件暴露',
    '漏洞代码' => '<pre>备份文件留在web目录</pre>',
    '攻击演示' => 'payload: config.php.bak',
    'payload详解' => 'config.php.bak<br>访问.bak',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '删除备份+禁止访问备份扩展名',
    '知识卡片' => '信息泄露·备份文件'
], __FILE__);
