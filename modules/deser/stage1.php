<?php
// 第 1 关：PHP反序列化
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/deser_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $data = $_POST['data'] ?? ''; $output = ''; $passed = false; $deser_out = '';
class Evil { public $cmd = ""; public function __wakeup() { if ($this->cmd) $GLOBALS["deser_out"] .= " 执行: ".$this->cmd; } }
if ($data) {
    if ($level === 'impossible') { $output = '安全：禁止反序列化用户输入'; }
    else { $obj = @unserialize($data); if ($obj === false) { $output = '反序列化失败'; } else { $output = '反序列化成功：' . print_r($obj, true) . $deser_out; $passed = is_object($obj) || is_array($obj); } }
}
if ($passed) pass_stage('deser', 1, 'deserialize');

deser_head(1, 'PHP反序列化', 'unserialize用户输入__wakeup触发');
?>
<form method="post" class="lab"><div class="row"><label>序列化数据</label><input type="text" name="data" value="<?= h($data) ?>" style="width:340px"></div><div class="row"><input type="submit" value="反序列化"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) deser_pass(true); ?>
<?php
deser_tail(['hint' => '构造序列化payload触发__wakeup', 'full' => 'O:4:"Evil":1:{s:3:"cmd";s:6:"whoami";}'], [
    '原理' => '反序列化第1关：unserialize用户输入__wakeup触发',
    '漏洞代码' => '<pre>unserialize($_POST["data"]);</pre>',
    '攻击演示' => 'payload: O:4:"Evil":1:{s:3:"cmd";s:6:"whoami";}',
    'payload详解' => 'O:4:"Evil":1:{s:3:"cmd";s:6:"whoami";}<br>提示：构造序列化payload触发__wakeup',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '不反序列化用户输入+用JSON',
    '知识卡片' => '反序列化·PHP反序列化'
], __FILE__);
