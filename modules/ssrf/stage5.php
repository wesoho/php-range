<?php
// 第 5 关：盲注SSRF
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ssrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $url = $_GET['url'] ?? ''; $output = ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { $output = '安全模式'; }
    else {
        $t0 = microtime(true);
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $resp = @file_get_contents($url, false, $ctx);
        $elapsed = microtime(true) - $t0;
        if ($resp === false) {
            $output = sprintf('请求失败（耗时 %.2fs）', $elapsed);
            if ($elapsed > 2.5) $passed = true;
        } else {
            $output = sprintf('请求成功（耗时 %.2fs，长度 %d）', $elapsed, strlen($resp));
            $passed = true;
        }
    }
}
if ($passed) pass_stage('ssrf', 5, 'url=' . $url);

ssrf_head(5, '盲注SSRF', '无回显，用响应时间判断端口开放');
?>
<form method="get" class="lab"><div class="row"><label>URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="请求"></div></form>
<?php if ($output): ?><div class="result"><h3>响应：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ssrf_pass(true); ?>
<?php
ssrf_tail(['hint' => '用响应时间判断端口是否开放', 'full' => 'url=http://127.0.0.1:3000/（开放则快速响应）'], [
    '原理' => 'SSRF 第5关：无回显内容，仅能通过响应时间判断',
    '漏洞代码' => '<pre>$resp = file_get_contents($url);
// 不输出resp，只看耗时</pre>',
    '攻击演示' => '访问开放端口快速响应，关闭端口超时',
    'payload详解' => '通过时间差判断内网端口状态',
    '工具实操' => '逐端口探测内网服务',
    '通关检测' => '响应成功或超时(>2.5s)即通关',
    '防御修复' => '禁止内网+超时设置+日志监控',
    '知识卡片' => '盲注SSRF用于内网端口扫描'
], __FILE__);
