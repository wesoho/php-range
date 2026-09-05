<?php
// 第 3 关：双引号闭合字符型注入 —— 对标 sqli-labs Less-3 变体
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
    $passed = sqli_check_pass($rows, 1);
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 3, $id);

sqli_head(3, '双引号闭合字符型注入', 'SQL：<code>WHERE id="' . h($id) . '"</code>（双引号包裹）。目标：闭合双引号并注入。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '用双引号 " 闭合前半段，再注入恒真条件，注意末尾引号配对。', 'full' => 'id=1" OR "1"="1'],
    [
        '原理' => '与单引号闭合同理，只是字符串用双引号包裹：<code>WHERE id="$id"</code>。需用 <code>"</code> 闭合。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=\"$id\"";</pre><span class="danger">↑ 双引号包裹但仍拼接</span>',
        '攻击演示' => '输入 id=1" OR "1"="1<br>SQL：WHERE id="1" OR "1"="1"<br>• " 闭合前双引号<br>• OR "1"="1 恒真<br>• 末尾 " 与 SQL 原有闭合引号配对',
        'payload详解' => '<code class="payload">1" OR "1"="1</code><br>与第2关相同，仅引号类型不同。也可用注释法：<code class="payload">-1" UNION SELECT id,username,email FROM sqli_users-- </code>',
        '工具实操' => 'sqlmap 自动识别引号类型：<code>sqlmap -u "stage3.php?id=1" --batch</code>',
        '通关检测' => '返回行数 &gt; 1 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval；impossible: PDO 预处理参数化。',
        '知识卡片' => '📌 双引号闭合在 MySQL 中较少见（MySQL 双引号默认是标识符引用），但 SQLite/PostgreSQL 中双引号是字符串。本靶场用 SQLite，双引号作字符串。',
    ],
    __FILE__
);
