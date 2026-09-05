<?php
// 第 5 关：不安全比较
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crypto_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $passed = false; $output = '';
$expected = '0e462097431903';
if ($input) {
    if ($level === 'impossible') { $match = hash_equals($expected, $input); $output = $match ? '匹配(安全比较)' : '不匹配'; }
    else { $match = ($input == $expected); $output = $match ? '匹配(==比较)' : '不匹配'; if ($match) $passed = true; }
}
if ($passed) pass_stage('crypto',5,'unsafe compare');
crypto_head(5, '不安全比较', '使用==而非hash_equals比较哈希');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) crypto_pass(true); ?>
<?php
crypto_tail(['hint' => '利用==类型转换绕过', 'full' => 'input=0e123456789'], [
    '原理' => '加密第5关：==可被绕过',
    '漏洞代码' => '<pre>($input == $expected)</pre>',
    '攻击演示' => 'payload: input=0e123',
    'payload详解' => 'input=0e123<br>类型转换',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '使用hash_equals严格比较',
    '知识卡片' => '加密·不安全比较'
], __FILE__);
