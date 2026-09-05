<?php
// 第 19 关：insert 注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$user = $_GET['user'] ?? '';
$rows = []; $err = ''; $passed = false;
try {
    $pdo = db();
    if ($user !== '') {
        if ($level === 'impossible' || $level === 'high') {
            $pdo->prepare("INSERT INTO sqli_users(username,password,email) VALUES(?,?,'x@x')")->execute([$user, 'pwd']);
        } else {
            if ($level === 'medium') $user = str_replace('SELECT', '', $user);
            $pdo->exec("INSERT INTO sqli_users(username,password,email) VALUES('$user','pwd','x@x')");
        }
    }
    $rows = $pdo->query("SELECT id,username,password FROM sqli_users WHERE username='test'")->fetchAll();
    $passed = ($level === 'low' || $level === 'medium') && sqli_check_pass($rows, 0, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 19, $user);

sqli_head(19, 'insert 注入', 'INSERT 语句的 VALUES 注入，用子查询把 admin 密码存入新行。');
sqli_form('user', $user);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '构造 ) 闭合 VALUES 后注入', 'full' => '1,(SELECT password FROM sqli_users WHERE id=1),\'a\',\'b\')--'],
    [
        '原理' => 'INSERT 注入：在 VALUES 中注入子查询，把敏感数据作为插入值。',
        '漏洞代码' => '<pre>$user = $_GET["user"];\n$pdo->exec("INSERT INTO sqli_users(username,password,email) VALUES(\'$user\',\'pwd\',\'x\')");</pre><span class="danger">↑ 参数直接拼入 INSERT</span>',
        '攻击演示' => '注册 user=test\',(select password from sqli_users where username=\'admin\'),\'x\')-- ，把 admin 密码存入新行 password 列，再查询 test 行验证。',
        'payload详解' => 'payload：<code class="payload">test\',(select password from sqli_users where username=\'admin\'),\'x\')-- </code><br>闭合 VALUES，用子查询把 flag 存入，-- 注释剩余。',
        '工具实操' => 'Burp Repeater 调试 payload。<br>注入后查询 test 行的 password 即得 flag。',
        '通关检测' => '新插入 test 行的 password 含 flag 即通关。',
        '防御修复' => 'medium: 过滤 SELECT 关键字（可用小写 select 绕过）；high/impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 insert 注入：在 INSERT VALUES 中注入子查询提取数据。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
