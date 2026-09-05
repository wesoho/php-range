<?php
// 第 4 关：XPath盲注
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ldap_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $output = ''; $passed = false;
if ($input) {
    if ($level === 'impossible') { $output = '安全'; }
    else { $match = strlen($input) > 2; $output = $match ? '匹配' : '不匹配'; if ($match) $passed = true; }
}
if ($passed) pass_stage('ldap',4,'input='.$input);
ldap_head(4, 'XPath盲注', '无回显XPath注入');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ldap_pass(true); ?>
<?php
ldap_tail(['hint' => '用响应判断匹配', 'full' => 'input=test'], [
    '原理' => 'XPath第4关：无回显判断',
    '漏洞代码' => '<pre>通过响应差异判断</pre>',
    '攻击演示' => 'payload: input=test',
    'payload详解' => 'input=test<br>盲注判断',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '转义+参数化',
    '知识卡片' => 'XPath·XPath盲注'
], __FILE__);
