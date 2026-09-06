<?php
// 第 2 关：过滤花括号
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssti_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $tpl = $_GET['tpl'] ?? ''; $output = ''; $passed = false;
if ($tpl) {
    if ($level === 'impossible') { $output = h($tpl); }
    else {
        $tpl_filtered = str_replace('{{','',$tpl);
        // 模拟模板引擎渲染表达式
        ob_start();
        try { $output = eval('return ' . $tpl_filtered . ';'); $passed = true; }
        catch (Throwable $e) { echo 'PHP 报错：' . $e->getMessage(); }
        ob_end_clean();
    }
}
if ($passed) pass_stage('ssti',2,'tpl='.$tpl);
ssti_head(2, '过滤花括号', '过滤{{但可用{ {或{${绕过');
?>
<form method="get" class="lab"><div class="row"><label>模板表达式</label><input type="text" name="tpl" value="<?= h($tpl) ?>" style="width:340px"></div><div class="row"><input type="submit" value="渲染"></div></form>
<?php if ($output !== false && $output !== null): ?><div class="result"><h3>渲染结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ssti_pass(true); ?>
<?php
ssti_tail(['hint' => '过滤了{{但可用其他语法', 'full' => 'tpl=7*7'], [
    '原理' => 'SSTI第2关：str_replace过滤{{',
    '漏洞代码' => '<pre>str_replace("{{","",$tpl)</pre>',
    '攻击演示' => 'payload: tpl=7*7',
    'payload详解' => 'tpl=7*7<br>绕过过滤',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '不eval+白名单表达式',
    '知识卡片' => 'SSTI·过滤花括号'
], __FILE__);
