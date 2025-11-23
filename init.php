<?php
//init.php
//Shared initialize file for all pages.

session_start();

//Simple file-based leaderboard (JSON)
define('LEADERBOARD_FILE', __DIR__ . '/leaderboard.json');

//Ensure leaderboard file exists
if (!file_exists(LEADERBOARD_FILE)) {
    file_put_contents(LEADERBOARD_FILE, json_encode([]), LOCK_EX);
}

/*Load leaderboard (array)*/
function load_leaderboard() {
    $json = @file_get_contents(LEADERBOARD_FILE);
    $arr = json_decode($json, true);
    if (!is_array($arr)) $arr = [];
    return $arr;
}

/*Save leaderboard array*/
function save_leaderboard($arr) {
    file_put_contents(LEADERBOARD_FILE, json_encode(array_values($arr), JSON_PRETTY_PRINT));
}

/*Add score to leaderboard*/
function add_leaderboard_entry($name, $cases_solved, $time_seconds) {
    if (trim($name) === '') {
        return false;
    }
    $lb = load_leaderboard();
    $lb[] = [
        'name' => $name,
        'cases' => (int)$cases_solved,
        'time' => (int)$time_seconds,
        'ts' => time()
    ];
    //NEW SORT ORDER:
    //  1. Highest Cases
    //  2. Lowest Time
    usort($lb, function($a,$b){
        if ($a['cases'] !== $b['cases']) {
            return $b['cases'] <=> $a['cases'];
        }
        //Lowest time wins
        return $a['time'] <=> $b['time'];
    });

    //keep top 50
    $lb = array_slice($lb, 0, 50);
    save_leaderboard($lb);
}

/*Initialize a new case (session-only)*/
function init_new_case() {
    //reset session case data
    $_SESSION['case'] = [
        'title' => 'Warehouse Homicide',
        'description' => "A body found in a disused warehouse. Look for clues, interrogate suspects and solve the mystery.",
        'started' => time(),
        'difficulty' => 2,
        'evidence_bag' => [], //evidence ids
        'suspects' => [
            1 => ['name'=>'Alex R.', 'profile'=>'Delivery driver. Nervous demeanor.', 'interviewed' => false, 'suspicion'=>0],
            2 => ['name'=>'Maya L.', 'profile'=>'Warehouse manager. Calm and clipped.', 'interviewed' => false, 'suspicion'=>0],
            3 => ['name'=>'Jon P.', 'profile'=>'Ex-employee. Loud and brash.', 'interviewed' => false, 'suspicion'=>0],
        ],
        'clues' => [
            1 => ['name'=>'Fingerprint fragment', 'desc'=>'Partial fingerprint on metal pipe.','collected'=>false],
            2 => ['name'=>'Receipt', 'desc'=>'Receipt for tools purchased two days prior.','collected'=>false],
            3 => ['name'=>'Thread', 'desc'=>'Red synthetic thread stuck to floorboard.','collected'=>false],
        ],
        'solved' => false,
        'fingerprint_completed' => false,
        'placements' => []
    ];
}

function update_difficulty() {
    $case =& $_SESSION['case'];

    $time_elapsed = time() - $case['started'];
    $clue_count = count($case['evidence_bag']);
    $interviews = 0;
    foreach($case['suspects'] as $s) {
        if ($s['interviewed']) $interviews++;
    }

    //Score model
    $score_factor = $clue_count + $interviews;

    //Dynamic scaling
    if ($score_factor >= 5 && $time_elapsed < 400) {
        $case['difficulty'] = 3;        //Hard
    } elseif ($score_factor >= 3) {
        $case['difficulty'] = 2;        //Medium
    } else {
        $case['difficulty'] = 1;        //Easy
    }
}

function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/* Check if the entire case is completed.
 * Requirements:
 *  1. All clues collected
 *  2. All suspects interviewed
 *  3. All clues placed in reconstruction
 *  4. Fingerprint puzzle successfully completed at least once
 * */
function check_case_completion() {
    $case =& $_SESSION['case'];

    //1. All clues collected
    foreach ($case['clues'] as $clue) {
        if (!$clue['collected']) {
            return false;
        }
    }

    //2. All suspects interviewed
    foreach ($case['suspects'] as $suspect) {
        if (!$suspect['interviewed']) {
            return false;
        }
    }

    //3. All clues placed in the reconstruction
    if (!isset($case['placements']) ||
        count($case['placements']) < count($case['clues'])) {
        return false;
    }

    //4. Fingerprint solved flag
    if (!isset($case['fingerprint_completed']) || $case['fingerprint_completed'] !== true) {
        return false;
    }

    return true;
}