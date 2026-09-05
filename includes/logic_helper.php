<?php
// 逻辑漏洞 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$LOGIC_STAGES = [1=>'水平越权', 2=>'垂直越权', 3=>'密码重置逻辑', 4=>'验证码绕过', 5=>'参数缺失', 6=>'整数溢出', 7=>'条件竞争', 8=>'空指针/默认值'];

function logic_head($n, $title, $desc) {
    global $LOGIC_STAGES;
    render_header("逻辑漏洞 第{$n}关", 'logic');
    render_stage_nav('logic', $LOGIC_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function logic_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function logic_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
