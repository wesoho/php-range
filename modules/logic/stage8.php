<?php
// 第 8 关：空指针/默认值
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $key = $_GET['key'] ?? ''; $output = ''; $passed = false;
if ($level === 'impossible') { if (empty($key) || !in_array($key, ['info','debug'])) { $output = '拒绝：无效key'; } else { $output = '访问：'.$key; } }
else { if (empty($key)) { $output = '使用默认配置：admin_debug_mode=true'; $passed = true; } else { $output = 'key='.$key; } }
if ($passed) pass_stage('logic',8,'key='.$key);

logic_head(8, '空指针/默认值', '参数为空时使用默认值绕过');
?>
<form method="get" class="lab"><div class="row"><label>Key(可留空)</label><input type="text" name="key" style="width:300px"></div><div class="row"><input type="submit" value="查询"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '参数为空使用默认值', 'full' => '不传key参数'], [
    '原理' => '逻辑漏洞第8关：参数为空用默认值',
    '漏洞代码' => '<pre>empty($key)时用默认值</pre>',
    '攻击演示' => 'payload: 不传key参数',
    'payload详解' => '不传key参数<br>留空触发默认',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '显式检查参数+禁止默认admin',
    '知识卡片' => '逻辑漏洞·空指针/默认值'
], __FILE__);
