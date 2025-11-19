<?php
session_start();
$users = file("users.txt", FILE_IGNORE_NEW_LINES);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $u = trim($_POST["user"]);
    $p = $_POST["pass"];

    foreach ($users as $line) {
        list($name, $hash) = explode("|", $line, 2);

        if ($name === $u && password_verify($p, $hash)) {
            $_SESSION["user"] = $u;
            header("Location: board.php");
            exit;
        }
    }
    echo "Invalid login";
}
?>
<head>
    <style> 
	body {
	font-family: Courier New;
	background: black;
	color: lime;
     }
</style>
</head>
<form method="POST">
    Username: <input name="user" required><br>
    Password: <input type="password" name="pass" required><br>
    <button>Login</button>
</form>
