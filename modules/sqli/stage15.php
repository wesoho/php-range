<?php
// 第 15 关：堆叠注入
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
    } else {
        // low/medium: exec 支持堆叠多语句（addslashes 对整数型堆叠无效）
        if ($level === 'medium') $id = addslashes($id);
        try { $pdo->exec("SELECT id,username,email FROM sqli_users WHERE id=$id"); } catch (Exception $e) {}
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=" . intval($id) . " OR username='hacked'")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 15, $id);

sqli_head(15, '堆叠注入', '用分号 ; 拼接第二条 SQL（SQLite 需 exec 多语句）。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '用 ; 拼接 INSERT/UPDATE', 'full' => '1; INSERT INTO sqli_users VALUES(99,\'hacked\',\'pwned\',\'x@x\')'],
    [
        '原理' => '堆叠注入：分号后执行任意 SQL。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=$id";</pre><span class="danger">↑ 参数直接拼接</span>',
        '攻击演示' => '1; INSERT ... 新增一行，再查询验证。PDO query 默认单语句，需用 exec。',
        'payload详解' => 'payload：<code class="payload">1; INSERT INTO sqli_users VALUES(99,\'hacked\',\'pwned\',\'x@x\')</code><br>根据闭合方式构造，闭合引号/括号后注入条件或 UNION。',
        '工具实操' => 'sqlmap -u "stage15.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 堆叠注入。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
