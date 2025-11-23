<?php
//interrogation.php
require __DIR__ . '/init.php';

if (!isset($_SESSION['case'])) { header('Location:index.php'); exit; }
$case =& $_SESSION['case'];

$suspect_id = isset($_GET['suspect']) ? (int)$_GET['suspect'] : (isset($_POST['suspect']) ? (int)$_POST['suspect'] : 0);
if (!isset($case['suspects'][$suspect_id])) {
    header('Location: case.php'); exit;
}

$s = &$case['suspects'][$suspect_id];
$line = '';
$feedback = '';

/* RANDOMIZE SUSPECT PERSONALITY (ONE TIME) */
if (!isset($s['personality'])) {
    //calm = never slips, nervous = likely slips, hostile = aggressive
    $types = ['calm', 'nervous', 'hostile'];
    shuffle($types);
    $s['personality'] = $types[0];
}

/* INITIALIZE DIALOGUE TREE STORAGE */
if (!isset($_SESSION['dialogue'])) $_SESSION['dialogue'] = [];
if (!isset($_SESSION['dialogue'][$suspect_id])) {
    $_SESSION['dialogue'][$suspect_id] = 'start';
}

$current = $_SESSION['dialogue'][$suspect_id];

/* BUILD DIALOGUE TREE FOR THIS SUSPECT */

$dialogue = [

   /* ---------------- START ---------------- */
   'start' => [
      'text' => "The suspect sits across from you. They seem {$s['personality']}.",
      'choices' => [
          'press' => 'press_lvl1',
          'friendly' => 'friendly_lvl1',
          'general' => 'general_lvl1'
      ],
      'suspicion' => 0
   ],

   /* ---------------- FIRST LAYER ---------------- */
   'press_lvl1' => [
      'text' => "You apply pressure. Their posture stiffens.",
      'choices' => [
          'press' => ($s['personality'] === 'calm' ? 'general_lvl1' : 'slip_up_1'),
          'friendly' => 'friendly_lvl1',
          'general' => 'general_lvl1'
      ],
      'suspicion' => ($s['personality'] === 'calm' ? +0 : +1)
   ],

   'friendly_lvl1' => [
      'text' => "You keep things friendly. They relax slightly.",
      'choices' => [
          'press' => 'press_lvl1',
          'general' => 'general_lvl1'
      ],
      'suspicion' => -0
   ],

   'general_lvl1' => [
      'text' => "You ask some routine questions. Their answers are vague.",
      'choices' => [
          'press' => 'press_lvl1',
          'friendly' => 'friendly_lvl1'
      ],
      'suspicion' => 0
   ],

   /* ---------------- SLIP-UP NODE ---------------- */
   'slip_up_1' => [
      'text' => "“I was with the victim— uh… no. No, I didn’t mean that.”",
      'choices' => [
          'press' => 'slip_up_caught',
          'friendly' => 'slip_up_soft',
          'general' => 'general_lvl2'
      ],
      'suspicion' => ($s['personality'] === 'nervous' ? +3 : +1)
   ],

   /* ---- CATCH THE LIE ---- */
   'slip_up_caught' => [
      'text' => "“I DIDN’T SAY THAT! You’re twisting my words!”",
      'choices' => [
          'press' => ($s['personality'] === 'hostile' ? 'panic_break' : 'panic_rise'),
          'friendly' => 'slip_up_soft'
      ],
      'suspicion' => +2
   ],

   /* ---- THEY TRY TO WALK IT BACK ---- */
   'slip_up_soft' => [
      'text' => "“Look… I just got mixed up. It’s been stressful.”",
      'choices' => [
          'press' => 'slip_up_caught',
          'general' => 'general_lvl2'
      ],
      'suspicion' => +0
   ],

   /* ---------------- SECOND LAYER ---------------- */
   'general_lvl2' => [
      'text' => "Their answers grow shorter. They keep glancing away.",
      'choices' => [
          'press' => 'press_lvl2',
          'friendly' => 'friendly_lvl2'
      ],
      'suspicion' => +1
   ],

   'press_lvl2' => [
      'text' => "Your tone sharpens. They visibly sweat.",
      'choices' => [
          'press' => ($s['personality'] === 'hostile' ? 'panic_break' : 'panic_rise'),
          'friendly' => 'friendly_lvl2'
      ],
      'suspicion' => ($s['personality'] === 'calm' ? +0 : +2)
   ],

   'friendly_lvl2' => [
      'text' => "They calm down a bit. “...Thanks for not yelling.”",
      'choices' => [
          'press' => 'press_lvl2',
          'general' => 'general_lvl2'
      ],
      'suspicion' => -0
   ],

   /* ---------------- PANIC RISING ---------------- */
   'panic_rise' => [
      'text' => "They’re rattled. “Look… I don’t know anything, okay?”",
      'choices' => [
          'press' => 'panic_break',
          'friendly' => 'friendly_lvl2'
      ],
      'suspicion' => +2
   ],

   /* ---------------- INTERROGATION BREAKDOWN ---------------- */
   'panic_break' => [
      'text' => "“I WANT A LAWYER.” The interrogation is over.",
      'choices' => [],
      'final' => true,
      'suspicion' => +4
   ]
];

/* PROCESS PLAYER CHOICE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice'])) {

    $choice = $_POST['choice'];

    //If no choice for this node, ignore
    if (!isset($dialogue[$current]['choices'][$choice])) {
        $line = "The suspect does not respond.";
    } else {
        $next = $dialogue[$current]['choices'][$choice];
        $_SESSION['dialogue'][$suspect_id] = $next;
        $current = $next;
    }

    //Apply suspicion modifier
    if (isset($dialogue[$current]['suspicion'])) {
        $s['suspicion'] += $dialogue[$current]['suspicion'];
        if ($s['suspicion'] < 0) $s['suspicion'] = 0;
    }

    //Mark as interviewed
    $s['interviewed'] = true;

    //Case score bonus
    $case['score'] += 2;
}

/* LOAD CURRENT TEXT */
$line = $dialogue[$current]['text'];
$is_final = isset($dialogue[$current]['final']) && $dialogue[$current]['final'] === true;
$choices = $dialogue[$current]['choices'] ?? [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Interrogation — <?= e($s['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <div class="header">
    <div class="title">Interrogation — <?= e($s['name']) ?></div>
    <div class="nav">
      <a href="case.php" class="btn">Case File</a>
      <a href="crime_scene.php" class="btn">Crime Scene</a>
      <a href="forensic.php" class="btn">Forensic Lab</a>
    </div>
  </div>

  <div class="panel">
    <div class="flex flex-gap-14">
      <div class="w-180">
        <div class="portrait-box"><img src="user.png"></div>
      </div>

      <div class="flex-1">
        <h3><?= e($s['name']) ?></h3>
        <p class="text-muted"><?= e($s['profile']) ?></p>

        <div class="inter-line">
          <span class="typewriter"><?= e($line) ?></span>
        </div>

        <?php if (!$is_final): ?>
        <form method="post" class="form-row mt-12">
          <input type="hidden" name="suspect" value="<?= $suspect_id ?>">

          <?php foreach ($choices as $label => $to): ?>
            <button class="btn" name="choice" value="<?= $label ?>">
              <?= ucfirst($label) ?>
            </button>
          <?php endforeach; ?>
        </form>
        <?php else: ?>
          <div class="text-muted mt-12">This interrogation is concluded.</div>
        <?php endif; ?>

        <div class="score-line">
          <strong>Suspicion:</strong> <?= e($s['suspicion']) ?> |
          <strong>Interviewed:</strong> <?= $s['interviewed'] ? 'Yes' : 'No' ?> |
          <strong>Personality:</strong> <?= e($s['personality']) ?>
        </div>

      </div>
    </div>

    <div class="return-wrap">
      <a class="btn" href="case.php">Return to Case File</a>
    </div>
  </div>
</div>
</body>
</html>