<?php
// SSRF 服务端请求伪造 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$SSRF_STAGES = [1=>'无过滤SSRF', 2=>'IP黑名单绕过', 3=>'协议绕过', 4=>'DNS重绑定', 5=>'盲注SSRF'];

function ssrf_head($n, $title, $desc) {
    global $SSRF_STAGES;
    render_header("SSRF 第{$n}关", 'ssrf');
    render_stage_nav('ssrf', $SSRF_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function ssrf_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function ssrf_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
