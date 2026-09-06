<?php
// 第 1 关：无过滤SSTI
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssti_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $tpl = $_GET['tpl'] ?? ''; $output = ''; $passed = false;
if ($tpl) {
    if ($level === 'impossible') { $output = h($tpl); }
    else {
        // 模拟模板引擎把 {{...}} 中的表达式交给 eval 渲染
        ob_start();
        try { $output = eval('return ' . $tpl . ';'); $passed = true; }
        catch (Throwable $e) { echo 'PHP 报错：' . $e->getMessage(); }
        ob_end_clean();
    }
}
if ($passed) pass_stage('ssti',1,'tpl='.$tpl);
ssti_head(1, '无过滤SSTI', '模板中直接eval用户输入');
?>
<form method="get" class="lab"><div class="row"><label>模板表达式</label><input type="text" name="tpl" value="<?= h($tpl) ?>" style="width:340px"></div><div class="row"><input type="submit" value="渲染"></div></form>
<?php if ($output !== false && $output !== null): ?><div class="result"><h3>渲染结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ssti_pass(true); ?>
<?php
ssti_tail(['hint' => '输入PHP表达式', 'full' => 'tpl=phpinfo()'], [
    '原理' => 'SSTI第1关：eval用户输入',
    '漏洞代码' => '<pre>eval($tpl);</pre>',
    '攻击演示' => 'payload: tpl=phpinfo()',
    'payload详解' => 'tpl=phpinfo()<br>输入表达式',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '不eval用户输入+沙箱',
    '知识卡片' => 'SSTI·无过滤'
], __FILE__);
