<?php
require_once APP_ROOT . '/includes/layout.php';
$CRYPTO_STAGES = [1=>'弱哈希', 2=>'硬编码密钥', 3=>'ECB模式', 4=>'弱随机数', 5=>'不安全比较'];
function crypto_head($n, $title, $desc) {
    global $CRYPTO_STAGES;
    render_header("不安全加密 第{$n}关", 'crypto');
    render_stage_nav('crypto', $CRYPTO_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function crypto_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function crypto_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
