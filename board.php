<?php
$storage = "posts.txt";

if (!file_exists($storage)) {
    file_put_contents($storage, "");
}

// Handle new post submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["content"])) {
    $content = trim($_POST["content"]);
    $entry = time() . "|" . str_replace("\n", "<br>", htmlspecialchars($content)) . "\n";
    file_put_contents($storage, $entry, FILE_APPEND);
}

// Load all posts
$posts = file($storage, FILE_IGNORE_NEW_LINES);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Message Board</title>
    <style>
    body {
        font-family: Courier New;
        background: black;
        color: lime;
    }

    textarea {
        background: black;
        color: lime;
        border: 2px solid lime;
        padding: 5px;
    }

    .post {
        border: 2px solid lime;
        padding: 10px;
        margin-bottom: 10px;
        background: black;
    }

    .time {
        font-size: 12px;
        color: #0f0;
        margin-bottom: 5px;
    }
</style>

</head>
<body>

<h2>Message Board</h2>

<form method="POST">
    <textarea name="content" rows="3" style="width:100%;" required></textarea><br>
    <button type="submit">Post</button>
</form>

<hr>

<h3>Posts:</h3>

<?php
foreach (array_reverse($posts) as $line) {
    list($timestamp, $text) = explode("|", $line, 2);
    echo "<div class='post'>";
    echo "<div class='time'>" . date("Y-m-d H:i:s", $timestamp) . "</div>";
    echo "<div>$text</div>";
    echo "</div>";
}
?>

</body>
</html>
