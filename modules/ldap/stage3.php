<?php
// 第 3 关：XPath注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ldap_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $output = ''; $passed = false;
if ($input) {
    if ($level === 'impossible') { $input = str_replace(array("'",'"','/','[',']'),'',$input); $output = '安全: '.$input; }
    else { $xpath = "//user[@name=$input]"; $output = 'XPath: '.$xpath; if (preg_match('/or\s+.?1.?=.?1/i', $input) || strpos($input,"']") !== false) $passed = true; }
}
if ($passed) pass_stage('ldap',3,'input='.$input);
ldap_head(3, 'XPath注入', 'XPath查询注入');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ldap_pass(true); ?>
<?php
ldap_tail(['hint' => '注入XPath表达式', 'full' => 'input=\' or \'1\'=\'1'], [
    '原理' => 'XPath第3关：XPath查询注入',
    '漏洞代码' => '<pre>"//user[@name=\'$input\']"</pre>',
    '攻击演示' => 'payload: input=\' or \'1\'=\'1',
    'payload详解' => 'input=\' or \'1\'=\'1<br>注入表达式',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '转义+参数化XPath',
    '知识卡片' => 'XPath·XPath注入'
], __FILE__);
