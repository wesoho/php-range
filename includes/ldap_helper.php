<?php
require_once APP_ROOT . '/includes/layout.php';
$LDAP_STAGES = [1=>'LDAP注入', 2=>'LDAP盲注', 3=>'XPath注入', 4=>'XPath盲注'];
function ldap_head($n, $title, $desc) {
    global $LDAP_STAGES;
    render_header("LDAP/XPath注入 第{$n}关", 'ldap');
    render_stage_nav('ldap', $LDAP_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function ldap_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function ldap_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
