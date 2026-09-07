<?php
// ============================================================
// 公共布局与 UI 框架 (Bootstrap 5 深色主题)
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once APP_ROOT . '/config.php';

// ---- 顶部导航与 HTML 头 ----
function render_header($title = '', $current_module = null) {
    global $MODULES;
    $level = get_level();
    $hint = get_hint();
    $user = $_SESSION['user'] ?? null;
    ?>
<!DOCTYPE html>
<html lang="zh-CN" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title ?: APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark border-bottom border-secondary sticky-top px-3" style="background:#0d1117">
  <a class="navbar-brand fw-bold" href="/index.php" style="color:#9fef00">
    <i class="bi bi-shield-lock-fill"></i> PHP-Range
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navModules">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navModules">
    <ul class="navbar-nav me-auto">
      <?php foreach ($MODULES as $key => $info): ?>
        <li class="nav-item">
          <a class="nav-link py-1 px-2 <?= $current_module===$key?'active fw-semibold':'' ?>" href="/modules/<?= $key ?>/index.php" style="font-size:13px"><?= h($info[0]) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if ($user): ?>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-person-fill"></i> <?= h($user) ?></span>
        <a href="/dashboard.php" class="btn btn-outline-secondary btn-sm" title="仪表盘"><i class="bi bi-speedometer2"></i></a>
        <a href="/mistakes.php" class="btn btn-outline-secondary btn-sm" title="错题本"><i class="bi bi-journal-x"></i></a>
        <a href="/quiz.php" class="btn btn-outline-secondary btn-sm" title="阶段测验"><i class="bi bi-patch-question"></i></a>
        <a href="/cheatsheet.php" class="btn btn-outline-secondary btn-sm" title="知识库"><i class="bi bi-book"></i></a>
        <a href="/logout.php" class="btn btn-outline-danger btn-sm" title="退出"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    <?php endif; ?>
  </div>
</nav>
<?php if ($user): ?>
<div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom border-secondary" style="background:#161b22;font-size:13px">
  <form method="post" action="/settings.php" class="d-flex align-items-center gap-2 mb-0">
    <span class="text-secondary"><i class="bi bi-shield-exclamation"></i> 难度</span>
    <select name="level" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
      <?php foreach (['low'=>'Low 无防护','medium'=>'Medium 可绕过','high'=>'High 强防护','impossible'=>'Impossible 不可攻破'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $level===$k?'selected':'' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <span class="text-secondary ms-2"><i class="bi bi-lightbulb"></i> 提示</span>
    <select name="hint" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
      <?php foreach (['none'=>'无','hint'=>'思路','full'=>'完整'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $hint===$k?'selected':'' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <span class="text-danger-emphasis ms-auto"><i class="bi bi-exclamation-triangle"></i> 仅供本地学习</span>
</div>
<?php endif; ?>
<main class="container-fluid px-4 py-3" style="max-width:1400px;margin:0 auto">
<?php
}

// ---- 底部 ----
function render_footer() {
    ?>
</main>
<footer class="text-center text-secondary py-3 border-top border-secondary mt-4" style="font-size:12px">
  PHP-Range 攻防靶场 · 仅供学习网络攻防 · 勿用于非法用途
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}

// ---- 消息提示 ----
function alert($msg, $type = 'info') {
    $bs_type = $type === 'fail' ? 'danger' : ($type === 'ok' ? 'success' : $type);
    echo '<div class="alert alert-'.$bs_type.'">'.h($msg).'</div>';
}

// ---- 提示显示（根据提示模式）----
function show_hint($hints) {
    $mode = get_hint();
    if ($mode === 'none') return;
    if ($mode === 'hint' && isset($hints['hint'])) {
        echo '<div class="alert alert-hint">💡 提示：'.h($hints['hint']).'</div>';
    }
    if ($mode === 'full' && isset($hints['full'])) {
        echo '<div class="alert alert-hint">💡 完整 payload：'.h($hints['full']).'</div>';
    }
}

// ---- 记录一次攻击尝试（成功则同时写入通关进度）----
// attempts 表是仪表盘"最近活动/失败次数"的数据来源，每关判定后都应调用
function record_attempt($module, $level_no, $passed, $payload = '') {
    if (empty($_SESSION['user'])) return;
    try {
        $pdo = db();
        $pdo->prepare("INSERT INTO attempts(user,module,level_no,passed,payload,ts) VALUES(?,?,?,?,?,datetime('now'))")
            ->execute([$_SESSION['user'], $module, $level_no, $passed?1:0, $payload]);
        if ($passed) {
            $pdo->prepare("INSERT OR REPLACE INTO progress(user,module,level_no,passed,ts) VALUES(?,?,?,1,datetime('now'))")
                ->execute([$_SESSION['user'], $module, $level_no]);
        }
    } catch (Exception $e) {}
}

// 记录通关（各关卡统一入口；evidence 作为 payload 存入 attempts 供复盘）
function pass_stage($module, $stage, $evidence = '') {
    record_attempt($module, $stage, true, $evidence);
}

// ---- 判断某关是否已通关 ----
function is_passed($module, $level_no) {
    if (!isset($_SESSION['user'])) return false;
    try {
        $st = db()->prepare("SELECT 1 FROM progress WHERE user=? AND module=? AND level_no=? AND passed=1");
        $st->execute([$_SESSION['user'], $module, $level_no]);
        return (bool)$st->fetch();
    } catch (Exception $e) { return false; }
}

// ---- 一次查询取回某模块全部已通关关卡号（供导航/徽章批量渲染）----
function passed_set($module) {
    static $cache = [];
    $user = $_SESSION['user'] ?? null;
    if (!$user) return [];
    $key = $user . '|' . $module;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $set = [];
    try {
        $st = db()->prepare("SELECT level_no FROM progress WHERE user=? AND module=? AND passed=1");
        $st->execute([$user, $module]);
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $no) $set[(int)$no] = true;
    } catch (Exception $e) {}
    $cache[$key] = $set;
    return $set;
}

// 八段式教程面板
function render_tutorial($sections) {
    $icons = ['原理'=>'📖','漏洞代码'=>'🐛','攻击演示'=>'⚔️','payload详解'=>'🔍','工具实操'=>'🛠️','通关检测'=>'✅','防御修复'=>'🛡️','知识卡片'=>'📌'];
    echo '<section class="tutorial"><h2>📚 八段式教学</h2>';
    $i = 0;
    foreach ($sections as $title => $body) {
        $i++;
        $ic = $icons[$title] ?? '📝';
        echo '<details class="tut-seg" ' . ($i<=2?'open':'') . '>';
        echo '<summary>' . $ic . ' ' . $i . '. ' . h($title) . '</summary>';
        echo '<div class="tut-body">' . $body . '</div></details>';
    }
    echo '</section>';
}

function render_source($file) {
    if (!is_file($file)) return;
    $code = file_get_contents($file);
    echo '<section class="source"><h2>👁️ 当前源码（' . h(basename($file)) . '）</h2>';
    echo '<pre class="code"><code>' . h($code) . '</code></pre></section>';
}

function render_hint($hints) {
    $mode = get_hint();
    if ($mode === 'none' || !isset($hints[$mode])) return;
    echo '<section class="hint"><h2>💡 提示（' . ($mode==='hint'?'思路':'完整 payload') . '）</h2>';
    echo '<div class="hint-body">' . $hints[$mode] . '</div></section>';
}

function render_stage_nav($module, $stages, $current) {
    echo '<nav class="stage-nav"><h3>关卡列表</h3><div class="stages">';
    foreach ($stages as $n => $info) {
        $cls = ($n==$current ? 'cur' : '') . (is_passed($module, $n) ? ' done' : '');
        echo '<a href="/modules/'.$module.'/stage'.$n.'.php" class="stage '.$cls.'" title="'.h($info).'">'.$n.'</a>';
    }
    echo '</div></nav>';
}
