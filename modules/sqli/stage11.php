<?php
// 第 11 关：布尔盲注-双引号
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
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=\"$id\"")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=\"$id\"")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=\"$id\"")->fetchAll();
    }
    // 盲注判定（仅 low/medium）：以恒真/恒假两次查询的可区分性判定
    if ($level !== 'impossible' && $level !== 'high') {
        $tpl = 'SELECT id,username,email FROM sqli_users WHERE id="$ID"';
        $sql_true  = str_replace('$ID', $id, $tpl);
        $sql_false = str_replace('$ID', sqli_blind_false_variant($id), $tpl);
        $rows_true2  = $pdo->query($sql_true)->fetchAll();
        $rows_false = $pdo->query($sql_false)->fetchAll();
        $passed = sqli_check_blind($rows_true2, $rows_false, $id);
    }
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 11, $id);

sqli_head(11, '布尔盲注-双引号', '双引号字符型布尔盲注。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '闭合双引号后布尔判断', 'full' => '1" AND "1"="1'],
    [
        '原理' => '双引号闭合同理。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id="$id"";</pre><span class="danger">↑ 参数直接拼接</span>',
        '攻击演示' => '1" AND "1"="1 真，1" AND "1"="2 假。',
        'payload详解' => 'payload：<code class="payload">1" AND "1"="1</code><br>根据闭合方式构造，闭合引号/括号后注入条件或 UNION。',
        '工具实操' => 'sqlmap -u "stage11.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 布尔盲注-双引号。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
