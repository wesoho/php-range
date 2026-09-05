<?php
// 第 5 关：Jinja2模板
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssti_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $tpl = $_GET['tpl'] ?? ''; $output = ''; $passed = false;
if ($tpl) {
    if ($level === 'impossible') { $output = h($tpl); }
    else { $tpl = str_replace(['{{','}}','config'],['','',"['secret']"],$tpl); $output = eval('return '.$tpl.';'); if ($output !== false) $passed = true; }
}
if ($passed) pass_stage('ssti',5,'tpl='.$tpl);
ssti_head(5, 'Jinja2模板', '模拟Jinja2模板引擎');
?>
<form method="get" class="lab"><div class="row"><label>Jinja2表达式</label><input type="text" name="tpl" value="<?= h($tpl) ?>" style="width:340px"></div><div class="row"><input type="submit" value="渲染"></div></form>
<?php if ($output !== false && $output !== null): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ssti_pass(true); ?>
<?php
ssti_tail(['hint' => 'Jinja2 {{config}} 语法', 'full' => 'tpl=7*7'], [
    '原理' => 'SSTI第5关：模拟Jinja2',
    '漏洞代码' => '<pre>模板引擎替换</pre>',
    '攻击演示' => 'payload: {{config}}',
    'payload详解' => '{{config}}<br>Jinja2语法',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '安全模板引擎+自动转义',
    '知识卡片' => 'SSTI·Jinja2模板'
], __FILE__);
