<?php
// 第 5 关：双引号+括号闭合
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
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=(\"$id\")")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=(\"$id\")")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=(\"$id\")")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 5, $id);

sqli_head(5, '双引号+括号闭合', 'SQL：WHERE id=("$id")。闭合双引号和括号。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '用 ") 闭合', 'full' => '1") OR ("1"="1'],
    [
        '原理' => '双引号+括号双层包裹。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=("$id")";</pre><span class="danger">↑ 参数直接拼接</span>',
        '攻击演示' => '输入 1") OR ("1"="1 → 恒真返回所有行。',
        'payload详解' => 'payload：<code class="payload">1") OR ("1"="1</code><br>根据闭合方式构造，闭合引号/括号后注入条件或 UNION。',
        '工具实操' => 'sqlmap -u "stage5.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 双引号+括号闭合。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
