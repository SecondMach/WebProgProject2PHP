<?php
//leaderboard.php
require __DIR__ . '/init.php';

$lb = load_leaderboard();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Leaderboard — Cryptic Quest</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <div class="header">
    <div class="title">Leaderboard</div>
    <div class="nav">
      <a href="index.php" class="btn">Home</a>
      <a href="case.php" class="btn">Case File</a>
      <a href="crime_scene.php" class="btn">Crime Scene</a>
    </div>
  </div>

  <div class="panel">
    <h3>Top Investigators</h3>

    <table class="table">
      <thead>
        <tr><th>Rank</th><th>Name</th><th>Cases</th><th>Time (s)</th></tr>
      </thead>
      <tbody>
        <?php if (empty($lb)): ?>
          <tr><td colspan="4" class="lb-empty">No scores yet.</td></tr>
        <?php else: ?>
          <?php $rank = 1; foreach($lb as $row): ?>
            <tr class="rank-<?= $rank <= 3 ? $rank : '' ?>">
              <td><?= $rank ?></td>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['cases']) ?></td>
              <td><?= e($row['time']) ?></td>
            </tr>
            <?php $rank++; endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="mt-12 text-muted">Sorted by highest cases and lowest time.</div>

    <div class="return-wrap">
      <a class="btn" href="index.php">Return Home</a>
    </div>
  </div>
</div>
</body>
</html>