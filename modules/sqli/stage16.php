<?php
// 第 16 关：二次注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$nick = $_GET['nick'] ?? '';
$rows = []; $err = ''; $passed = false;
try {
    $pdo = db();
    $esc = function($s) { return str_replace("'", "''", $s); };
    if ($level === 'impossible') {
        if ($nick !== '') {
            $pdo->prepare("INSERT OR REPLACE INTO profiles(id,username,nickname) VALUES(99,'user',?)")->execute([$nick]);
        }
        $st = $pdo->prepare("SELECT id,username,nickname FROM profiles WHERE username=?");
        $st->execute(['admin']);
        $rows = $st->fetchAll();
    } else {
        if ($nick !== '') {
            $pdo->exec("INSERT OR REPLACE INTO profiles(id,username,nickname) VALUES(99,'user','".$esc($nick)."')");
        }
        $r = $pdo->query("SELECT nickname FROM profiles WHERE id=99")->fetch();
        $stored = $r ? $r['nickname'] : '';
        if ($stored !== '') {
            if ($level === 'high') $stored = $esc($stored);
            elseif ($level === 'medium') $stored = str_replace('--', '', $stored);
            $rows = $pdo->query("SELECT id,username,nickname FROM profiles WHERE username='".$stored."'")->fetchAll();
        }
    }
    $passed = ($level === 'low' || $level === 'medium') && sqli_check_pass($rows, 1, 'admin');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 16, $nick);

sqli_head(16, '二次注入', '先注册恶意 nickname（如 admin\' OR \'1\'=\'1），查看资料时拼入 SQL 触发注入。');
sqli_form('nick', $nick);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '先注册恶意 nickname 再触发', 'full' => 'nick=admin\' OR \'1\'=\'1'],
    [
        '原理' => '二次注入：数据存入时正确转义，读取拼入 SQL 时未转义而触发。',
        '漏洞代码' => '<pre>// 存入(转义): INSERT INTO profiles(nickname) VALUES(esc($nick))\n// 读取(拼接): WHERE username=\'.$$row["nickname"].\'</pre><span class="danger">↑ 读取时直接拼接</span>',
        '攻击演示' => '1. 注册 nick=admin\' OR \'1\'=\'1（存入合法）<br>2. 查看资料时拼成 WHERE username=\'admin\' OR \'1\'=\'1\'<br>3. 返回所有行 → 通关',
        'payload详解' => 'payload：<code class="payload">nick=admin\' OR \'1\'=\'1</code><br>存入时转义为合法值，读取时拼接触发注入。',
        '工具实操' => 'Burp Repeater 调试 payload。<br>分两步：先 GET ?nick=... 注册，再访问触发读取。',
        '通关检测' => '返回行数 &gt; 1 或结果含 admin 即通关。',
        '防御修复' => 'medium: 过滤 -- 注释符（可用 OR 绕过）；high: 读取时也转义单引号；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 二次注入：存入与使用点过滤不一致。关联：闭合方式 → 报错/盲注 → WAF 绕过。真实案例：SQL 注入长期位居 OWASP Top 10。',
    ],
    __FILE__
);
