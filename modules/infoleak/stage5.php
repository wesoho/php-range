<?php
// 第 5 关：配置文件
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/infoleak_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$configs = ['/workspace/php-range/config.php','/workspace/php-range/data/init.sql'];
foreach ($configs as $c) { if (file_exists($c)) { $output .= '配置文件: '.$c.' (存在)
'; $passed = true; } }
if ($passed) pass_stage('infoleak',5,'config leak');
infoleak_head(5, '配置文件', '配置文件可被直接访问');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查配置"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) infoleak_pass(true); ?>
<?php
infoleak_tail(['hint' => '访问config文件', 'full' => '/config.php'], [
    '原理' => '信息泄露第5关：配置可访问',
    '漏洞代码' => '<pre>配置文件在web目录</pre>',
    '攻击演示' => 'payload: /config.php',
    'payload详解' => '/config.php<br>访问config',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '配置放web目录外+禁止访问',
    '知识卡片' => '信息泄露·配置文件'
], __FILE__);
