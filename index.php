<?php
//index.php
require __DIR__ . '/init.php';

//If "New Case" posted, start a new case
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'new_case') {
        init_new_case();
        header('Location: case.php'); exit;
    } elseif ($_POST['action'] === 'load_case') {
        //load existing session if present - redirect to case
        if (isset($_SESSION['case'])) {
            header('Location: case.php'); exit;
        } else {
            //inform user
            $message = "No saved progress found. Start a New Case.";
        }
    } elseif ($_POST['action'] === 'reset') {
        session_unset();
        session_destroy();
        session_start();
        $message = "Session cleared.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cryptic Quest — Homepage</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- APP WRAPPER -->
  <div class="app">

    <!-- HEADER -->
    <div class="header">
      <div class="title">Cryptic Quest: Crime Scene Investigation</div>
      <div class="nav">
        <a href="leaderboard.php" class="btn">Leaderboard</a>

        <form method="post" class="form-row">
          <button class="btn" name="action" value="new_case">New Case</button>
          <button class="btn" name="action" value="load_case">Load Saved Progress</button>
          <button class="btn" name="action" value="reset">Reset Session</button>
        </form>
      </div>
    </div>

    <!-- MAIN CONTENT BELOW HERO -->
    <div class="panel">
      <div class="flex flex-gap-18 items-center">

        <!-- LEFT SIDE — BUTTONS + TEXT -->
        <div class="flex-1">

          <p class="text-muted">
            Step into the role of an investigator. Collect evidence, interrogate suspects,
            and reconstruct the scene — all using server-side PHP and CSS only.
          </p>

          <?php if (!empty($message)): ?>
            <div class="text-accent mt-10"><?= e($message) ?></div>
          <?php endif; ?>
        </div>

        <!-- RIGHT SIDE — QUICK MENU -->
        <div class="w-260">
          <div class="panel quick-panel">
            <h3 class="mt-8 mb-8">Quick Menu</h3>
            <p class="evidence-item">• Start new case</p>
            <p class="evidence-item">• Enter Crime Scene</p>
            <p class="evidence-item">• Visit Forensic Lab</p>
          </div>
        </div>
      </div>
    </div>

    <!-- HERO SECTION -->
    <div class="hero">
        <h1 class="crime-title">

          <!-- TEXT THAT TYPES -->
          <span class="crime-text">
              A CRIME HAS BEEN COMMITTED...
          </span>

          <!-- BLOOD DRIPS -->
          <div class="drip"></div>
          <div class="drip"></div>
          <div class="drip"></div>
          <div class="drip"></div>
          <div class="drip"></div>

        </h1>

        <div class="interrogation-scene">
            <div class="chair left-chair"></div>
            <div class="inter-table"></div>
            <div class="chair right-chair"></div>
        </div>
    </div>

    <div class="footer-note">No JavaScript — server only</div>

  </div>
</body>
</html>