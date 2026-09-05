<?php
require_once APP_ROOT . '/includes/layout.php';
$SESSION_STAGES = [1=>'会话固定', 2=>'会话预测', 3=>'会话超时', 4=>'Cookie属性', 5=>'会话劫持'];
function session_head($n, $title, $desc) {
    global $SESSION_STAGES;
    render_header("会话管理 第{$n}关", 'session');
    render_stage_nav('session', $SESSION_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function session_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function session_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
