<?php
require_once APP_ROOT . '/includes/layout.php';
$REDIRECT_STAGES = [1=>'无过滤重定向', 2=>'白名单绕过', 3=>'相对路径绕过'];
function redirect_head($n, $title, $desc) {
    global $REDIRECT_STAGES;
    render_header("开放重定向 第{$n}关", 'redirect');
    render_stage_nav('redirect', $REDIRECT_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function redirect_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function redirect_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
