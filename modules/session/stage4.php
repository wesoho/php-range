<?php
// 第 4 关：Cookie属性
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/session_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$params = session_get_cookie_params();
$output = 'HttpOnly: '.($params['httponly']?'是':'否').' Secure: '.($params['secure']?'是':'否').' SameSite: '.($params['samesite'] ?? '无');
if (!$params['secure'] || !$params['httponly']) { $output .= ' (Cookie属性不安全！)'; $passed = true; }
if ($passed) pass_stage('session',4,'cookie attrs');
session_head(4, 'Cookie属性', 'Cookie无HttpOnly/Secure');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查Cookie"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) session_pass(true); ?>
<?php
session_tail(['hint' => 'Cookie缺少安全属性', 'full' => '检查Cookie属性'], [
    '原理' => '会话第4关：无HttpOnly/Secure',
    '漏洞代码' => '<pre>Cookie无安全属性</pre>',
    '攻击演示' => 'payload: 检查Cookie',
    'payload详解' => '检查Cookie<br>检查属性',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '设置HttpOnly+Secure+SameSite',
    '知识卡片' => '会话·Cookie属性'
], __FILE__);
