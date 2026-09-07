<?php
// 第 14 关：时间盲注-双引号
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
    // 时间盲注判定（仅 low/medium）：注入的 randomblob 让 SQL 耗时显著增加
    if ($level !== 'impossible' && $level !== 'high') {
        $tpl = 'SELECT id,username,email FROM sqli_users WHERE id="$ID"';
        $t0 = microtime(true);
        $rows = $pdo->query(str_replace('$ID', $id, $tpl))->fetchAll();
        $elapsed_ms = round((microtime(true) - $t0) * 1000);
        $passed = $elapsed_ms >= 150 && stripos($id, 'randomblob') !== false;
    }
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 14, $id);

sqli_head(14, '时间盲注-双引号', '双引号时间盲注。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '双引号闭合+randomblob延时', 'full' => '1" AND randomblob(100000000)="1'],
    [
        '原理' => '双引号时间盲注同理。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id="$id"";</pre><span class="danger">↑ 参数直接拼接</span>',
        '攻击演示' => '1" AND randomblob(100000000)="1 真时延时。',
        'payload详解' => 'payload：<code class="payload">1" AND randomblob(100000000)="1</code><br>根据闭合方式构造，闭合引号/括号后注入条件或 UNION。',
        '工具实操' => 'sqlmap -u "stage14.php?id=1" --batch --dbs<br>Burp Repeater 调试 payload。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可被宽字节绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 时间盲注-双引号。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
