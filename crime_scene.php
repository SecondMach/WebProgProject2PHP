<?php
//crime_scene.php
require __DIR__ . '/init.php';

if (!isset($_SESSION['case'])) {
    header('Location: index.php'); exit;
}

$case =& $_SESSION['case'];
$msg = '';
$collectedClass = '';

//Ensure placements array exists
if (!isset($case['placements'])) {
  $case['placements'] = [];
}

//Assign Random Positions Once
if (!isset($case['clue_positions_assigned'])) {

    $position_pool = [
        'pos-top-left',      //Top-left shelf
        'pos-workbench',     //Green workbench area
        'pos-floorboard',    //Brown floorboard/pallet
        'pos-corner-right',  //Top-right corner shelf
        'pos-middle',        //Center aisle
        'pos-toolbox',       //Red pallet jack/tool area
        'pos-shadows',       //Dark shadows between racks
        'pos-doorway'        //Doorway/white roll-up shutter
    ];

    shuffle($position_pool);

    $i = 0;
    foreach ($case['clues'] as $id => &$clue) {
      if ($id == 3) {
        $clue['pos_class'] = 'pos-middle';
        continue;
      }
      $clue['pos_class'] = $position_pool[$i] ?? $position_pool[0];
      $i++;
    }

    $case['clue_positions_assigned'] = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect'])) {
    $id = (int)$_POST['collect'];
    if (isset($case['clues'][$id]) && !$case['clues'][$id]['collected']) {
        //collect it
        $case['clues'][$id]['collected'] = true;
        $case['evidence_bag'][] = $id;
        $case['score'] += 10;
        $msg = "Collected: " . $case['clues'][$id]['name'];
        $collectedClass = 'success';
    } else {
        $msg = "Already collected or invalid.";
        $collectedClass = 'error';
    }
    update_difficulty();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_clue'])) {

    $cid = (int)$_POST['clue'];
    $loc = $_POST['location'];

    if (!isset($case['clues'][$cid])) {
        $msg = "Invalid clue selection.";
        $class = "error";
    } else {
        $case['placements'][$cid] = $loc;

        if ($loc === $case['clues'][$cid]['pos_class']) {
            $msg = "Correct placement for: " . $case['clues'][$cid]['name'];
            $case['score'] += 10;
            $class = "success";
        } else {
            $msg = "Incorrect placement.";
            $case['score'] = max(0, $case['score'] - 5);
            $class = "error";
        }

        update_difficulty();
    }
}

$location_names = [
    'pos-top-left'   => "Top-left shelving",
    'pos-workbench'  => "On the workbench",
    'pos-floorboard' => "By the floorboard pallet",
    'pos-corner-right'=> "Upper-right shelving",
    'pos-middle'     => "Center aisle",
    'pos-toolbox'    => "Near the toolbox / pallet jack",
    'pos-shadows'    => "Shadowed shelf area",
    'pos-doorway'    => "Right-side doorway"
];

//Difficulty wrapper class
$difficulty_class = "difficulty-" . (int)$case['difficulty'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Crime Scene — <?= e($case['title']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $difficulty_class ?>">
<div class="app">
  <div class="header">
    <div class="title">Crime Scene</div>
    <div class="nav">
      <a href="case.php" class="btn">Case File</a>
      <a href="forensic.php" class="btn">Forensic Lab</a>
      <a href="leaderboard.php" class="btn">Leaderboard</a>
    </div>
  </div>

  <div class="panel scene <?= $collectedClass ?>">
    <h3>Warehouse Crime Scene</h3>
    <p class="text-muted">Click the clues hidden in the crime scene to collect them.</p>

    <?php if ($msg): ?>
      <div class="scene-msg <?= $collectedClass === 'success' ? 'text-accent' : 'text-danger' ?>">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>

    <div class="scene relative">
        <img src="crime_scenebg.avif" class="scene-bg" alt="Crime Scene">

        <?php foreach($case['clues'] as $id => $cl): ?>
            <?php if (!$cl['collected']): ?>

                <form method="post"
                      class="clue <?= e($cl['pos_class']) ?>">
                    <input type="hidden" name="collect" value="<?= $id ?>">

                    <?php if ($case['difficulty'] == 1): ?>
                        <!-- EASY MODE -->
                        <button class="clue-btn">
                          <img src="evidence<?= $id ?>.png" class="clue-icon" alt="">
                        </button>

                    <?php else: ?>
                        <!-- MEDIUM/HARD: clickable image -->
                        <button class="clue-btn">
                          <img src="evidence<?= $id ?>.png" class="clue-icon" alt="">
                        </button>
                    <?php endif; ?>
                </form>

            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- SECTION 2: RECONSTRUCT THE SCENE -->
    <h4 class="mt-20">Crime Scene Reconstruction</h4>
    <p class="text-muted">Choose which clue belongs in which location.</p>

    <form method="post" class="mt-12">

      <label>Clue:</label>
      <select name="clue" class="input-field">
        <option value="">-- Select Clue --</option>
        <?php foreach ($case['clues'] as $id=>$cl): ?>
          <option value="<?= $id ?>" <?= isset($_POST['clue']) && $_POST['clue'] == $id ? 'selected' : '' ?>><?= e($cl['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label class="mt-8">Location:</label>
      <select name="location" class="input-field">
        <option value="">-- select --</option>
          <?php foreach ($location_names as $class=>$label): ?>
            <option value="<?= $class ?>"
              <?= ($_POST['location'] ?? '') == $class ? "selected" : "" ?>>
              <?= e($label) ?>
            </option>
          <?php endforeach; ?>
      </select>

      <button class="btn mt-12" name="place_clue">Submit Placement</button>
    </form>


    <h4 class="mt-20">Your Reconstruction</h4>
    <div class="panel">
        <?php if (empty($case['placements'])): ?>
            <p class="text-muted">No placements made yet.</p>
        <?php else: ?>
            <?php foreach ($case['placements'] as $cid=>$loc): ?>
                <div class="evidence-item">
                    <strong><?= e($case['clues'][$cid]['name']) ?></strong>
                    — <?= e($location_names[$loc] ?? $loc) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="return-wrap">
      <a class="btn" href="case.php">Return to Case File</a>
    </div>
  </div>
</div>
</body>
</html>