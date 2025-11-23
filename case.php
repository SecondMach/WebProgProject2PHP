<?php
//case.php
require __DIR__ . '/init.php';

if (!isset($_SESSION['case'])) {
    //no active case, redirect to homepage
    header('Location: index.php'); exit;
}

//handle actions: go to crime scene or interrogation or forensic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['goto']) && $_POST['goto'] === 'crime') {
        header('Location: crime_scene.php'); exit;
    }
    if (isset($_POST['goto']) && $_POST['goto'] === 'forensic') {
        header('Location: forensic.php'); exit;
    }
    if (isset($_POST['interrogate']) && is_numeric($_POST['interrogate'])) {
        $sid = (int)$_POST['interrogate'];
        header('Location: interrogation.php?suspect=' . $sid); exit;
    }
}

$case = $_SESSION['case'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Case File — <?= e($case['title']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <div class="header">
    <div class="title">Case File — <?= e($case['title']) ?></div>
    <div class="nav">
      <a href="index.php" class="btn">Home</a>
      <a href="crime_scene.php" class="btn">Crime Scene</a>
      <a href="forensic.php" class="btn">Forensic Lab</a>
      <a href="leaderboard.php" class="btn">Leaderboard</a>
    </div>
  </div>

  <div class="panel">
    <h3>Case Summary</h3>
    <p class="text-muted"><?= nl2br(e($case['description'])) ?></p>

    <div class="flex flex-gap-16 mt-12">
      <div class="flex-1">
        <h4>Suspects</h4>

        <div class="suspects-grid">
          <?php foreach($case['suspects'] as $id => $s): ?>
            <div class="suspect panel">
              <div class="suspect-portrait"><img src="user.png"></div>

              <h4><?= e($s['name']) ?></h4>
              <p class="text-muted"><?= e($s['profile']) ?></p>

              <form method="post" class="form-inline">
                <input type="hidden" name="interrogate" value="<?= $id ?>">
                <button class="btn" type="submit">Interrogate</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="w-320">
        <h4>Evidence Bag</h4>
        <div class="evidence-bag-container">
          <div class="evidence-bag">
            <div class="evidence-bag-label">Hover over to view evidence</div>
          </div>
          <div class="evidence-items">
            <?php
              $bag = $case['evidence_bag'] ?? [];
              if (empty($bag)):
            ?>
              <div class="evidence-empty">No evidence collected yet.</div>
            <?php else: ?>
              <?php foreach($bag as $eid): ?>
                <?php $c = $case['clues'][$eid]; ?>
                <div class="evidence-item">
                  <div><?= e($c['name']) ?></div>
                  <div class="text-muted"><?= e($c['desc']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="mt-12">
          <form method="post" class="form-row">
            <button class="btn" name="goto" value="crime">Go to Crime Scene</button>
            <button class="btn" name="goto" value="forensic">Forensic Lab</button>
          </form>
        </div>

        <div class="score-line mt-14">
          <strong>Score:</strong> <?= e($case['score']) ?> |
          <strong>Difficulty:</strong> <?= e($case['difficulty']) ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>