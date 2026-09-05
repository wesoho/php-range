<?php
// 第 3 关：会话超时
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/session_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$last = $_SESSION['last_activity'] ?? 0;
$timeout = $level === 'impossible' ? 1800 : 0;
if ($timeout > 0 && (time() - $last) > $timeout) { session_destroy(); $output = '会话已超时'; }
else { $_SESSION['last_activity'] = time(); $output = '会话活跃（无超时限制）'; $passed = true; }
if ($passed) pass_stage('session',3,'session timeout');
session_head(3, '会话超时', '无超时机制会话永不过期');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="检查超时"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) session_pass(true); ?>
<?php
session_tail(['hint' => '会话永不过期', 'full' => '长时间不操作后仍有效'], [
    '原理' => '会话第3关：无超时机制',
    '漏洞代码' => '<pre>不检查最后活动时间</pre>',
    '攻击演示' => 'payload: 长时间后仍有效',
    'payload详解' => '长时间后仍有效<br>永不过期',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '设置session.gc_maxlifetime',
    '知识卡片' => '会话·会话超时'
], __FILE__);
