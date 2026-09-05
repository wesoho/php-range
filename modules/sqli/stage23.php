<?php
// 第 23 关：WAF-内联注释
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$id = $_GET['id'] ?? '1';
$rows = []; $err = ''; $passed = false;
try {
    $pdo = db();
    if ($level === 'impossible') {
        $st = $pdo->prepare("SELECT id,username,email FROM sqli_users WHERE id=?");
        $st->execute([$id]); $rows = $st->fetchAll();
    } elseif ($level === 'high') {
        $id = intval($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 23, $id);

sqli_head(23, 'WAF-内联注释', 'WAF 过滤 UNION/SELECT，用内联注释 /*!...*/ 绕过。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '用 /*!UNION*/ 绕过关键字过滤', 'full' => '1 /*!UNION*/ /*!SELECT*/ 1,2,password FROM sqli_users'],
    [
        '原理' => 'MySQL 内联注释 /*!...*/ 执行内容但绕过 WAF 关键字匹配。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=$id";</pre><span class="danger">↑ 参数直接拼接</span>',
        '攻击演示' => '/*!UNION*/ 在 MySQL 执行为 UNION，WAF 可能不识别。',
        'payload详解' => 'payload：<code class="payload">1 /*!UNION*/ /*!SELECT*/ 1,2,password FROM sqli_users</code><br>根据闭合方式构造，闭合引号/括号后注入条件或 UNION。',
        '工具实操' => 'sqlmap -u "stage23.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 WAF-内联注释。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
