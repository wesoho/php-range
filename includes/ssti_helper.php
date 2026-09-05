<?php
require_once APP_ROOT . '/includes/layout.php';
$SSTI_STAGES = [1=>'无过滤SSTI', 2=>'过滤花括号', 3=>'过滤eval', 4=>'Twig模板', 5=>'Jinja2模板'];
function ssti_head($n, $title, $desc) {
    global $SSTI_STAGES;
    render_header("SSTI 第{$n}关", 'ssti');
    render_stage_nav('ssti', $SSTI_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function ssti_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function ssti_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
