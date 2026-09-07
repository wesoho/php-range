<?php
// 第 4 关：过滤反引号
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/cmdi_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$ip = $_GET['ip'] ?? '127.0.0.1';
$output = '';
$passed = false;

if ($ip !== '') {
    if ($level === 'impossible') {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $output = '无效 IP 地址';
        } else {
            $cmd = 'ping -c 1 ' . escapeshellarg($ip) . ' 2>&1';
            @exec($cmd, $out, $ret);
            $output = is_array($out) ? implode("\n", $out) : $out;
        }
    } elseif ($level === 'high') {
        $cmd = 'ping -c 1 ' . escapeshellarg($ip) . ' 2>&1';
        @exec($cmd, $out, $ret);
        $output = is_array($out) ? implode("\n", $out) : $out;
    } else {
        $ip = str_replace('`', '', $ip);
        $cmd = 'ping -c 1 ' . $ip . ' 2>&1';
        @exec($cmd, $output, $ret);
        $output = is_array($output) ? implode("\n", $output) : $output;
        $passed = cmdi_check_pass($output);
    }
}

if ($passed) pass_stage('cmdi', 4, 'ip=' . $ip);

cmdi_head(4, '过滤反引号', '过滤反引号 ` 但可用 $() 绕过。');
?>
<form method="get" class="lab">
  <div class="row"><label>IP 地址</label><input type="text" name="ip" value="<?= h($ip) ?>" style="width:340px"></div>
  <div class="row"><input type="submit" value="Ping"></div>
</form>
<?php if ($passed) cmdi_pass(true); ?>
<?php if ($output): ?>
  <div class="result">
    <h3>命令输出：</h3>
    <pre class="code"><?= h($output) ?></pre>
  </div>
<?php endif; ?>
<?php
cmdi_tail(
    ['hint' => '过滤了反引号，用 $() 绕过', 'full' => '127.0.0.1;$(id)　或　127.0.0.1&whoami'],
    [
    '原理' => '命令注入第 4 关：过滤反引号 ` 但可用 $() 绕过。<br>OS 命令注入：用户输入被拼接到系统命令中，攻击者通过特殊字符（; | & $() ` 等）注入额外命令。',
    '漏洞代码' => '<pre>$ip = $_GET["ip"];
        $ip = str_replace(\'`\', \'\', $ip);
$cmd = "ping -c 1 " . $ip;
exec($cmd, $output);</pre><span class="danger">↑ 输入直接拼入系统命令</span>',
    '攻击演示' => '1. 输入 127.0.0.1 正常 ping<br>2. 输入 127.0.0.1;$(id)<br>3. 观察输出中是否包含注入命令结果 → 通关',
    'payload详解' => '<code class="payload">127.0.0.1;$(id)</code><br>提示：过滤了反引号，用 $() 绕过<br>命令注入核心：用分隔符（; | & 等）结束原命令，开始新命令。',
    '工具实操' => 'curl "http://靶场/stage4.php?ip=127.0.0.1;id"<br>Burp Suite Repeater 调试 payload<br>注意：不同操作系统命令分隔符不同',
    '通关检测' => '输出中包含 root: /etc/ uid= Linux 等系统信息标记即通关。',
    '防御修复' => '<b>high 级</b>：escapeshellarg() 转义参数<br><b>impossible 级</b>：白名单验证输入（如 IP 格式校验），不拼接命令<br><pre>$ip = escapeshellarg($ip); // 转义
// 或更好：白名单验证
if (!filter_var($ip, FILTER_VALIDATE_IP)) die("无效IP");</pre>',
    '知识卡片' => '📌 命令注入 = RCE（远程代码执行）<br>OWASP A03:2021-Injection<br>分隔符：; | & || && $() ` %0a<br>本关演示：过滤反引号'
],
    __FILE__
);
