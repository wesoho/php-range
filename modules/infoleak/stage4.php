<?php
// 第 4 关：源码泄露
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/infoleak_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$vcs = ['/workspace/.git/config','/workspace/.svn/entries','/workspace/.hg/store'];
foreach ($vcs as $v) { if (file_exists($v)) { $output .= '发现VCS: '.$v.'
'; $passed = true; } }
if (!$passed) { $output = '检查.git/.svn/.hg目录（如存在则源码泄露）'; $passed = true; }
if ($passed) pass_stage('infoleak',4,'source leak');
infoleak_head(4, '源码泄露', '.git/.svn目录暴露源码');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查VCS"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) infoleak_pass(true); ?>
<?php
infoleak_tail(['hint' => '访问.git目录', 'full' => '/.git/config'], [
    '原理' => '信息泄露第4关：VCS目录暴露',
    '漏洞代码' => '<pre>VCS目录留在web目录</pre>',
    '攻击演示' => 'payload: /.git/config',
    'payload详解' => '/.git/config<br>访问.git',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '删除VCS目录+禁止访问',
    '知识卡片' => '信息泄露·源码泄露'
], __FILE__);
