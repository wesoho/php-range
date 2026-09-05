<?php
// 第 5 关：会话劫持
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/session_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
if (isset($_GET['sid'])) {
    if ($level === 'impossible') { $output = '安全：绑定IP/UA检查'; }
    else { $output = '使用传入的SID: '.$_GET['sid'].' (无IP/UA绑定，可劫持)'; $passed = true; }
} else { $output = '当前SID: '.session_id().' (可传入sid参数劫持)'; }
if ($passed) pass_stage('session',5,'session hijack');
session_head(5, '会话劫持', '无IP/UA绑定可劫持');
?>
<form method="get" class="lab"><div class="row"><label>SID</label><input type="text" name="sid" style="width:340px"></div><div class="row"><input type="submit" value="劫持"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) session_pass(true); ?>
<?php
session_tail(['hint' => '传入他人SID劫持会话', 'full' => 'sid=其他用户的sessionID'], [
    '原理' => '会话第5关：无IP/UA绑定',
    '漏洞代码' => '<pre>不验证会话与用户绑定</pre>',
    '攻击演示' => 'payload: 传入他人SID',
    'payload详解' => '传入他人SID<br>劫持SID',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '绑定IP+UA+定期重新认证',
    '知识卡片' => '会话·会话劫持'
], __FILE__);
