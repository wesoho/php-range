<?php
// 第 3 关：错误信息
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/infoleak_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
if (ini_get('display_errors')) { $output = 'display_errors=On 错误信息会暴露：
- 服务器路径
- PHP版本
- 代码片段
- 数据库结构'; $passed = true; }
else { $output = 'display_errors=Off'; }
if ($passed) pass_stage('infoleak',3,'error info');
infoleak_head(3, '错误信息', 'display_errors暴露路径和代码');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查错误配置"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) infoleak_pass(true); ?>
<?php
infoleak_tail(['hint' => '错误信息暴露敏感数据', 'full' => '触发错误看详细信息'], [
    '原理' => '信息泄露第3关：display_errors=On',
    '漏洞代码' => '<pre>display_errors=On</pre>',
    '攻击演示' => 'payload: 触发错误',
    'payload详解' => '触发错误<br>看错误信息',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '生产环境关闭display_errors',
    '知识卡片' => '信息泄露·错误信息'
], __FILE__);
