<?php
// 第 2 关：IP黑名单绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $url = $_GET['url'] ?? ''; $output = ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $output = '安全模式：禁止内网'; }
    else {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $blacklist = ['127.0.0.1','localhost','0.0.0.0'];
        if (in_array($host, $blacklist)) { $output = '拒绝：黑名单IP'; }
        else {
            $output = @file_get_contents($url) ?: '请求失败';
            if (stripos($output,'root:') !== false || stripos($output,'[fonts]') !== false || stripos($output,'for 16-bit') !== false) $passed = true;
        }
    }
}
if ($passed) pass_stage('ssrf', 2, 'url=' . $url);

ssrf_head(2, 'IP黑名单绕过', '禁止127.0.0.1但可用0.0.0.0或十进制绕过');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="请求"></div></form>
<?php if ($output): ?><div class="result"><h3>响应：</h3><pre class="code"><?= h(mb_substr($output,0,2000)) ?></pre></div><?php endif; ?>
<?php if ($passed) ssrf_pass(true); ?>
<?php
ssrf_tail(['hint' => '黑名单不含0.0.0.0或十进制IP', 'full' => 'url=file:///etc/passwd　或　url=file:///C:/Windows/win.ini（file协议无host不触发黑名单）'], [
    '原理' => 'SSRF 第2关：检查host是否在黑名单，但file://协议无host可绕过',
    '漏洞代码' => '<pre>if (in_array($host, $blacklist)) die("拒绝");
echo file_get_contents($url);</pre>',
    '攻击演示' => '用file://协议绕过host检查',
    'payload详解' => 'file:///etc/passwd 的host为空，不触发黑名单',
    '工具实操' => '尝试不同协议绕过host检查',
    '通关检测' => '响应中包含root:即通关',
    '防御修复' => '检查协议+解析IP+禁止内网',
    '知识卡片' => '黑名单不完整=无效防护，白名单才安全'
], __FILE__);
