<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once APP_ROOT . '/includes/layout.php';

if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

render_header('通关进度', null);
$user = $_SESSION['user'];
global $MODULES;

echo '<h2>📊 通关进度</h2>';

foreach ($MODULES as $mkey => $minfo) {
    [$mname, $mdesc, $mcount] = $minfo;
    $st = db()->prepare("SELECT COUNT(*) FROM progress WHERE user=? AND module=? AND passed=1");
    $st->execute([$user, $mkey]);
    $passed = $st->fetchColumn();
    $pct = $mcount > 0 ? round($passed / $mcount * 100) : 0;
    echo '<div class="card">';
    echo '<h3>'.h($mname).' <small>('.intval($passed).'/'.intval($mcount).' 关 · '.$pct.'%)</small></h3>';
    echo '<div class="progress-bar"><div class="progress-fill" style="width:'.$pct.'%"></div></div>';
    echo '<div class="stage-grid">';
    for ($i = 1; $i <= $mcount; $i++) {
        $done = is_passed($mkey, $i);
        echo '<a href="/modules/'.$mkey.'/stage'.$i.'.php" class="stage-badge '.($done?'done':'todo').'">'.$i.'</a>';
    }
    echo '</div></div>';
}

echo '<div class="card"><h3>📝 最近尝试记录</h3>';
$logs = db()->prepare("SELECT module,level_no,passed,payload,ts FROM attempts WHERE user=? ORDER BY ts DESC LIMIT 20");
$logs->execute([$user]);
$rows = $logs->fetchAll();
if ($rows) {
    echo '<table class="result"><tr><th>模块</th><th>关卡</th><th>结果</th><th>payload</th><th>时间</th></tr>';
    foreach ($rows as $r) {
        echo '<tr><td>'.h($r['module']).'</td><td>'.intval($r['level_no']).'</td>';
        echo '<td>'.($r['passed']?'✅':'❌').'</td>';
        echo '<td>'.h(mb_substr($r['payload']??'',0,60)).'</td><td>'.h($r['ts']).'</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p>暂无尝试记录。</p>';
}
echo '</div>';

render_footer();
