# WebProgProject2PHP - Cryptic Quest
A Detective Game made entirely through PHP, HTML, and CSS

Cryptic Quest is a web-based detective game where players investigate a crime, interview suspects, collect evidence, analyze forensic data, and aim to solve the case as efficiently as possible. The game features branching dialogue, a crime scene investigation interface, a fingerprint matching mini-game, and a leaderboard system.
________________________________________
Table of Contents
1.	Game Structure
    o	Session Initialization & Management (init.php)
    o	Index/Home (index.php)
    o	Case File (case.php)
    o	Crime Scene (crime_scene.php)
    o	Interrogation (interrogation.php)
    o	Forensic Lab (forensic.php)
    o	Leaderboard (leaderboard.php)
3.	Styling & User Interface (style.css)
4.	Gameplay Overview
________________________________________
Game Structure
Session Initialization & Management (init.php)
•	Initializes sessions and sets up the current case.
•	Tracks:
  o	Suspects and their personalities.
  o	Collected clues.
  o	Fingerprint analysis completion.
  o	Case score and completion status.
•	Handles leaderboard functionality.
________________________________________
Index/Home (index.php)
•	Landing page for the game.
•	Allows the player to start a new case.
•	Shows recent leaderboard or game instructions.
________________________________________
Case File (case.php)
•	Displays an overview of the current case.
•	Shows collected clues and interviewed suspects.
•	Allows navigation to:
  o	Crime Scene (crime_scene.php)
  o	Interrogation (interrogation.php)
  o	Forensic Lab (forensic.php)
________________________________________
Crime Scene (crime_scene.php)
•	Interactive crime scene image with clickable clues.
•	Clues are displayed based on difficulty:
  o	Easy: Highlighted glow.
  o	Medium: Clickable but visible.
  o	Hard: Invisible until hovered.
•	Clicking clues adds them to the evidence bag for the case.
•	Uses .scene-clue and .clue-icon classes from style.css.
________________________________________
Interrogation (interrogation.php)
•	Allows players to interrogate suspects.
•	Features:
  o	Randomized suspect personalities (calm, nervous, hostile).
  o	Branching dialogue with choices:
    •	Press
    •	Friendly
    •	General
  o	Dialogue affects suspicion levels.
  o	Typewriter effect for dialogue text (.typewriter).
•	Suspects can “slip up” or refuse to continue, impacting case score.
•	Tracks interview completion per suspect.
________________________________________
Forensic Lab (forensic.php)
•	Mini-game for fingerprint analysis.
•	Player compares an unknown print to a candidate print:
  o	Match
  o	Don't Match
•	Correct matches increase case score, incorrect choices reduce score.
•	Completion is required to solve the case.
•	On case completion, allows score submission to the leaderboard.
________________________________________
Leaderboard (leaderboard.php)
•	Displays top investigators.
•	Tracks:
  o	Player name
  o	Number of cases solved
  o	Time taken per case
•	Sorted by most cases solved and fastest completion time.
•	Highlights top 3 ranks in gold, silver, and bronze.
________________________________________
Styling & User Interface (style.css)
•	Global Theme: Dark, moody theme with accent colors for highlights, success, and danger states.
•	Layout & Panels: Flexible grid and flexbox layouts, rounded panels, borders, and shadows.
•	Buttons & Forms: Hover effects, scaling on click, and consistent form input styles.
•	Interrogation: Typewriter dialogue, portrait boxes, score and feedback lines.
•	Crime Scene: Absolute-positioned clues, hover effects, and difficulty-based visibility.
•	Forensic Lab: Fingerprint containers styled with borders and shadows.
•	Leaderboard: Styled tables with top rank highlighting.
•	Animations: Typing effects for crime titles, blinking cursor, and blood drip animations.
•	Evidence Bag: Interactive dropdown for collected evidence.
Key Classes: .panel, .btn, .typewriter, .scene-clue, .portrait-box, .fingerprint-print, .table, .evidence-bag.
________________________________________
Gameplay Overview
1.	Start a New Case: Begin in index.php and view the case file.
2.	Explore Crime Scene: Navigate to crime_scene.php and collect clues.
3.	Interview Suspects: Use interrogation.php to interact with suspects. Personality and dialogue choices affect suspicion levels.
4.	Forensic Analysis: Use forensic.php to analyze fingerprints. Correct analysis is required to solve the case.
5.	Solve Case & Submit Score: Once all tasks are complete, the case is marked solved, and scores can be submitted to the leaderboard.
6.	Leaderboard Tracking: See your rank among other investigators in leaderboard.php.

This file was created and improvised with the help of AI.
