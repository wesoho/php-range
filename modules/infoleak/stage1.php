<?php
// 第 1 关：目录列表
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/infoleak_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$dir = '/workspace/php-range/public/uploads';
$files = glob($dir.'/*'); $output = '目录内容：
';
foreach ($files as $f) { $output .= basename($f).' ('.filesize($f).' B)
'; }
if (count($files) > 0) $passed = true;
if ($passed) pass_stage('infoleak',1,'dir listing');
infoleak_head(1, '目录列表', '开启目录浏览暴露文件列表');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="列出目录"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) infoleak_pass(true); ?>
<?php
infoleak_tail(['hint' => '目录浏览暴露文件', 'full' => '访问目录URL'], [
    '原理' => '信息泄露第1关：开启目录浏览',
    '漏洞代码' => '<pre>开启目录列表</pre>',
    '攻击演示' => 'payload: 访问目录',
    'payload详解' => '访问目录<br>列出文件',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '关闭目录浏览+放index.html',
    '知识卡片' => '信息泄露·目录列表'
], __FILE__);
