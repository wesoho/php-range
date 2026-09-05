<?php
// 第 2 关：POP链构造
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/deser_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $data = $_POST['data'] ?? ''; $output = ''; $passed = false; $deser_out = '';
class A { public $obj; public function __wakeup() { if ($this->obj) $GLOBALS["deser_out"] .= " A->".$this->obj; } } class B { public $cmd; public function __toString() { return "B(".$this->cmd.")"; } }
if ($data) {
    if ($level === 'impossible') { $output = '安全：禁止反序列化用户输入'; }
    else { $obj = @unserialize($data); if ($obj === false) { $output = '反序列化失败'; } else { $output = '反序列化成功：' . print_r($obj, true) . $deser_out; $passed = is_object($obj) || is_array($obj); } }
}
if ($passed) pass_stage('deser', 2, 'deserialize');

deser_head(2, 'POP链构造', '多类__wakeup->__toString链式调用');
?>
<form method="post" class="lab"><div class="row"><label>序列化数据</label><input type="text" name="data" value="<?= h($data) ?>" style="width:340px"></div><div class="row"><input type="submit" value="反序列化"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) deser_pass(true); ?>
<?php
deser_tail(['hint' => '构造POP链A->B', 'full' => 'O:1:"A":1:{s:3:"obj";O:1:"B":1:{s:3:"cmd";s:4:"id";}}'], [
    '原理' => '反序列化第2关：多类__wakeup->__toString链式调用',
    '漏洞代码' => '<pre>unserialize($_POST["data"]);</pre>',
    '攻击演示' => 'payload: O:1:"A":1:{s:3:"obj";O:1:"B":1:{s:3:"cmd";s:4:"id";}}',
    'payload详解' => 'O:1:"A":1:{s:3:"obj";O:1:"B":1:{s:3:"cmd";s:4:"id";}}<br>提示：构造POP链A->B',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '不反序列化用户输入+用JSON',
    '知识卡片' => '反序列化·POP链构造'
], __FILE__);
