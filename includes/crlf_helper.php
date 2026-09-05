<?php
require_once APP_ROOT . '/includes/layout.php';
$CRLF_STAGES = [1=>'HTTP响应拆分', 2=>'邮件注入', 3=>'日志注入'];
function crlf_head($n, $title, $desc) {
    global $CRLF_STAGES;
    render_header("CRLF注入 第{$n}关", 'crlf');
    render_stage_nav('crlf', $CRLF_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function crlf_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function crlf_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
