<?php
// 第 3 关：过滤eval
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssti_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $tpl = $_GET['tpl'] ?? ''; $output = ''; $passed = false;
if ($tpl) {
    if ($level === 'impossible') { $output = h($tpl); }
    else {
        if (stripos($tpl,'eval') === false) {
            // 模拟模板引擎渲染表达式
            ob_start();
            try { $output = eval('return ' . $tpl . ';'); $passed = true; }
            catch (Throwable $e) { echo 'PHP 报错：' . $e->getMessage(); }
            ob_end_clean();
        } else { $output = '过滤了eval'; }
    }
}
if ($passed) pass_stage('ssti',3,'tpl='.$tpl);
ssti_head(3, '过滤eval', '过滤eval但可用assert');
?>
<form method="get" class="lab"><div class="row"><label>表达式</label><input type="text" name="tpl" value="<?= h($tpl) ?>" style="width:340px"></div><div class="row"><input type="submit" value="渲染"></div></form>
<?php if ($output !== false && $output !== null): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ssti_pass(true); ?>
<?php
ssti_tail(['hint' => '过滤了eval但可用其他函数', 'full' => 'tpl=system("id")'], [
    '原理' => 'SSTI第3关：过滤eval关键字',
    '漏洞代码' => '<pre>stripos($tpl,"eval")</pre>',
    '攻击演示' => 'payload: tpl=system("id")',
    'payload详解' => 'tpl=system("id")<br>用其他函数',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用危险函数+不eval',
    '知识卡片' => 'SSTI·过滤eval'
], __FILE__);
