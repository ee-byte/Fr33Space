<?php
$storage = "posts.txt";

if (!file_exists($storage)) {
    file_put_contents($storage, "");
}

// Get next ID
function next_id($storage) {
    $lines = file($storage, FILE_IGNORE_NEW_LINES);
    if (empty($lines)) return 1;
    $last = explode("|", end($lines))[0];
    return intval($last) + 1;
}

// Handle submit
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["content"])) {
    $content = trim($_POST["content"]);
    $content = str_replace("\n", "<br>", htmlspecialchars($content));

    $id = next_id($storage);
    $parent = isset($_POST["parent"]) ? intval($_POST["parent"]) : 0;

    if ($parent < 0) $parent = 0;

    $entry = $id . "|" . time() . "|" . $content . "|" . $parent . "\n";
    file_put_contents($storage, $entry, FILE_APPEND);
}

// Load posts
$raw = file($storage, FILE_IGNORE_NEW_LINES);

// Build simple posts array
$posts = [];
foreach ($raw as $line) {
    $parts = explode("|", $line);
    if (count($parts) !== 4) continue;

    list($id, $timestamp, $text, $parent) = $parts;
    $id = intval($id);
    $parent = intval($parent);

    if ($parent === $id) $parent = 0;

    $posts[$id] = [
        "id" => $id,
        "time" => intval($timestamp),
        "text" => $text,
        "parent" => $parent
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Board</title>
<style>
    body { background:black; color:lime; font-family:Courier New; }
    .post { border:1px solid lime; padding:10px; margin-top:10px; }
    textarea, button { background:black; color:lime; border:1px solid lime; }
    .reply-container { margin-left:25px; display:none; }
    .reply-form { display:none; margin-top:10px; }
    .toggle-btn { cursor:pointer; color: lime; text-decoration:underline; }
</style>

<script>
function toggleReplies(id) {
    let box = document.getElementById("replies-" + id);
    box.style.display = (box.style.display === "block") ? "none" : "block";
}

function toggleReplyForm(id) {
    let form = document.getElementById("replyform-" + id);
    form.style.display = (form.style.display === "block") ? "none" : "block";
}
</script>

</head>
<body>

<h2>Message Board</h2>

<form method="POST">
    <textarea name="content" rows="3" style="width:100%;" required></textarea><br>
    <button type="submit">Post</button>
</form>

<hr>

<?php
// Render a single post without recursion
function render_post($posts, $id, $level = 0) {
    $p = $posts[$id];

    // Count replies
    $reply_count = 0;
    foreach ($posts as $child) {
        if ($child["parent"] == $id) $reply_count++;
    }

    echo "<div class='post' style='margin-left:" . ($level * 25) . "px'>";
    echo "<div><b>" . date('Y-m-d H:i:s', $p['time']) . "</b></div>";
    echo "<div>{$p['text']}</div>";

    // Show replies button
    if ($reply_count > 0) {
        echo "<div class='toggle-btn' onclick='toggleReplies($id)'>
                Show Replies ($reply_count)
              </div>";
    }

    // Reply button
    echo "<div class='toggle-btn' onclick='toggleReplyForm($id)'>
            Reply
          </div>";

    // Hidden reply form
    echo "<form id='replyform-$id' class='reply-form' method='POST'>
            <input type='hidden' name='parent' value='{$p['id']}'>
            <textarea name='content' rows='2' style='width:90%;'></textarea><br>
            <button type='submit'>Post Reply</button>
          </form>";

    // Replies hidden block
    echo "<div id='replies-$id' class='reply-container'>";

    foreach ($posts as $child) {
        if ($child["parent"] == $id) {
            render_post($posts, $child["id"], $level + 1);
        }
    }

    echo "</div></div>";
}

// Output top-level posts
foreach (array_reverse($posts) as $p) {
    if ($p["parent"] == 0) {
        render_post($posts, $p["id"]);
    }
}
?>

</body>
</html>
