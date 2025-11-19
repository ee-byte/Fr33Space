<?php
session_start();
// sends user to login/checks for account
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
// user and post data storage(local)
$user = $_SESSION["user"];
$storage = "posts.txt";

// Create file if missing
if (!file_exists($storage)) {
    file_put_contents($storage, "");
}

// Handle posting
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["content"])) {
    $content = trim($_POST["content"]);
    $parent = isset($_POST["parent"]) ? intval($_POST["parent"]) : 0;

    $entry = time() . "|" . $user . "|" .
             str_replace("\n", "<br>", htmlspecialchars($content)) .
             "|" . $parent . "\n";

    file_put_contents($storage, $entry, FILE_APPEND);
}

// Load and parse posts
$raw = file($storage, FILE_IGNORE_NEW_LINES);
$posts = [];
$idCounter = 1;

foreach ($raw as $line) {
    $parts = explode("|", $line, 4);
    if (count($parts) < 3) continue;

    $timestamp = intval($parts[0]);
    $postUser  = $parts[1];
    $text      = $parts[2];
    $parent    = isset($parts[3]) ? intval($parts[3]) : 0;

    $posts[$idCounter] = [
        "id"     => $idCounter,
        "time"   => $timestamp,
        "user"   => $postUser,
        "text"   => $text,
        "parent" => $parent
    ];

    $idCounter++;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Message Board</title>
<style>
body { background:black; color:lime; font-family:Courier New, monospace; }
.post { border:1px solid lime; padding:10px; margin-top:10px; }
textarea, button { background:black; color:lime; border:1px solid lime; }
details { margin-left:20px; margin-top:5px; }
</style>
</head>
<body>

<h2>Message Board</h2>

<form method="POST">
    <textarea name="content" rows="3" style="width:100%;" required></textarea><br>
    <button type="submit">Post</button>
</form>

<hr>

<?php
// Recursive post renderer
function render_post($posts, $id, $level = 0) {
    $p = $posts[$id];

   echo "<div class='post' style='margin-left:" . ($level * 25) . "px'>";

    echo "<b>" . date("Y-m-d H:i:s", $p["time"]) . "</b><br>";
    echo "<b>[{$p['user']}]</b><br>";
    echo $p["text"] . "<br><br>";


    // Reply form (hidden under <details>)
    echo "<details><summary>Reply</summary>";
    echo "<form method='POST'>
            <input type='hidden' name='parent' value='{$p['id']}'>
            <textarea name='content' rows='2' style='width:90%;'></textarea><br>
            <button type='submit'>Post Reply</button>
          </form>";
    echo "</details>";

 // Count replies
$reply_count = 0;
foreach ($posts as $child) {
    if ($child["parent"] == $id) $reply_count++;
}

// If there are replies, hide them inside <details>
if ($reply_count > 0) {
    echo "<details><summary>Replies ($reply_count)</summary>";
    foreach ($posts as $child) {
        if ($child["parent"] == $id) {
            render_post($posts, $child["id"], $level + 1);
        }
    }
    echo "</details>";
}


    echo "</div>";
}

// Display top-level posts
foreach (array_reverse($posts) as $p) {
    if ($p["parent"] == 0) {
        render_post($posts, $p["id"]);
    }
}
?>

</body>
</html>
