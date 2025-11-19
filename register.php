<?php
session_start();
$users = "users.txt";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $u = trim($_POST["user"]);
    $p = password_hash($_POST["pass"], PASSWORD_DEFAULT);

    // prevent breaking the file format
    if (strpos($u, "|") !== false) { die("Invalid username"); }

    file_put_contents($users, "$u|$p\n", FILE_APPEND);
    echo "Registered. <a href='login.php'>Login</a>";
    exit;
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
    <button>Sign in</button>
</form>
