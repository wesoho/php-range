<?php
// 第 24 关：WAF-等价函数双写
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
        // WAF: 大小写不敏感过滤 union/select（单次替换，可用双写绕过）
        $id = str_ireplace(['union', 'select'], '', $id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 24, $id);

sqli_head(24, 'WAF-等价函数双写', 'WAF 大小写不敏感过滤 union，用 UNIunionON 双写绕过（过滤后剩 UNION）。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '双写关键字绕过单次过滤', 'full' => '1 UNIunionON SELselectECT 1,2,password FROM sqli_users'],
    [
        '原理' => '双写：str_ireplace 只做一次替换，UNIunionON 被删去中间 union 后恰好拼回 UNION。等价函数思路同理：MySQL 里 information_schema 被禁可用 innodb_table_stats 等价表。',
        '漏洞代码' => '<pre>$id = str_ireplace("union", "", $id);<br>$sql = "SELECT ... WHERE id=$id";</pre><span class="danger">↑ 单次替换可被双写打穿</span>',
        '攻击演示' => 'WAF 单次替换 union 为空，UNIunionON → UNION。',
        'payload详解' => 'payload：<code class="payload">1 UNIunionON SELselectECT 1,2,password FROM sqli_users</code><br>注意 SELselectECT 中的 select 未被过滤，双写只是保险写法；若 WAF 同时过滤 union/select 则两处都需双写。',
        '工具实操' => 'sqlmap -u "stage24.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。WAF 侧应循环替换直到无匹配，或改用白名单。',
        '知识卡片' => '📌 双写绕过利用的是"单次替换"的实现缺陷。关联：内联注释（23）→ 双写（本关）→ 大小写（25）。',
    ],
    __FILE__
);
