<?php
//forensic.php
require __DIR__ . '/init.php';
if (!isset($_SESSION['case'])) { header('Location:index.php'); exit; }

$case =& $_SESSION['case'];
$msg = '';
$last_result = null;

//Available fingerprint images
$fingerprints = [
    "fp1.png",
    "fp2.png",
    "fp3.png",
    "fp4.png",
    "fp5.png",
    "fp6.png"
];

//If case is solved, disable fingerprint game immediately
if ($case['solved']) {
    unset($_SESSION['fp_unknown'], $_SESSION['fp_candidate'], $_SESSION['fp_is_match']);
}

//On first load: randomize prints
if (!$case['solved'] && !isset($_SESSION['fp_unknown'])) {

    //Unknown print = random image
    $_SESSION['fp_unknown'] = $fingerprints[array_rand($fingerprints)];

    //Decide if the second print matches or not
    $_SESSION['fp_is_match'] = rand(0,1) === 1;

    if ($_SESSION['fp_is_match']) {
        //same image
        $_SESSION['fp_candidate'] = $_SESSION['fp_unknown'];
    } else {
        //different image
        do {
            $img = $fingerprints[array_rand($fingerprints)];
        } while ($img === $_SESSION['fp_unknown']);
        $_SESSION['fp_candidate'] = $img;
    }
}

//Handle player choice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$case['solved'] && isset($_POST['answer'])) {

    $player_says_match = ($_POST['answer'] === "match");
    $actual_match = $_SESSION['fp_is_match'];

    if ($player_says_match === $actual_match) {
        $msg = "Correct! Fingerprint evaluation successful.";
        $last_result = 'success';
        $case['score'] += 20;
        update_difficulty();

        //Mark fingerprint step completed
        $case['fingerprint_completed'] = true;

        //Check full-case completion
        if (check_case_completion()) {
          $case['solved'] = true;
          //Time taken
          $time = time() - $case['started'];

          //Add to leaderboard
          add_leaderboard_entry($_POST['player_name'], 1, $time);
        }
    } else {
        $msg = "Incorrect fingerprint assessment.";
        $last_result = 'fail';
        $case['score'] = max(0, $case['score'] - 10);
    }

    //regenerate for next round
    unset($_SESSION['fp_unknown'], $_SESSION['fp_candidate'], $_SESSION['fp_is_match']);

    //redirect so the new round initializes properly
    header("Location: forensic.php");
    exit;
}

//Submit score after solved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_score']) && $case['solved']) {
    $name = trim($_POST['player_name'] ?? 'Investigator');
    $time_seconds = time() - $case['started'];
    add_leaderboard_entry($name, 1, $time_seconds);

    $msg = "Score submitted. Good job, " . e($name);
    //Redirect to leaderboard after submission for confirmation and to avoid resubmission
    header('Location: leaderboard.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forensic Lab — Fingerprint Match</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">

  <div class="header">
    <div class="title">Forensic Lab</div>
    <div class="nav">
      <a href="case.php" class="btn">Case File</a>
      <a href="crime_scene.php" class="btn">Crime Scene</a>
      <a href="leaderboard.php" class="btn">Leaderboard</a>
    </div>
  </div>

  <div class="panel">
    <h3>Fingerprint Comparison</h3>
    <p class="text-muted">Look at the two prints. Are they the same?</p>

    <?php if (!$case['solved']): ?>
      <div class="text-muted mb-12">
          Required to finish the case:
          <ul>
              <li>Collect all clues</li>
              <li>Interview all suspects</li>
              <li>Reconstruct the crime scene</li>
              <li>Complete fingerprint analysis</li>
          </ul>
      </div>
    <?php endif; ?>

    <?php if ($msg): ?>
      <div class="mt-8 <?= $last_result ?>">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>

    <?php if (!$case['solved']): ?>

    <div class="flex flex-gap-14">
      <!-- Unknown print -->
      <div class="panel flex-1">
        <h4>Unknown Print</h4>
        <div class="fingerprint-print">
            <img src="<?= $_SESSION['fp_unknown'] ?>" width="120">
        </div>
      </div>

      <!-- Candidate print -->
      <div class="panel flex-1">
        <h4>Candidate Print</h4>
        <div class="fingerprint-print">
            <img src="<?= $_SESSION['fp_candidate'] ?>" width="120">
        </div>
      </div>
    </div>

    <form method="post" class="mt-12 center-row">
        <button name="answer" value="match" class="btn">MATCH</button>
        <button name="answer" value="no_match" class="btn">DON'T MATCH</button>
    </form>

    <?php endif; ?>

    <?php if ($case['solved']): ?>
        <div class="feedback-line mt-12">Case Solved! Submit to leaderboard:</div>
        <form method="post" class="form-col mt-8">
          <input type="text" name="player_name" class="input-field" required>
          <button class="btn" name="submit_score" value="1">Submit Score</button>
        </form>
    <?php endif; ?>

    <div class="return-wrap">
      <a href="case.php" class="btn">Return to Case File</a>
    </div>

  </div>

</div>
</body>
</html>