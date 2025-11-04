<?php
session_start();
require 'db_connect.php';
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($name && $email && $pass) {
        $hash = hash('sha256',$pass);
        $stmt = $conn->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
        $stmt->bind_param('sss',$name,$email,$hash);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $err = 'Failed to register (email may already exist).';
        }
    } else $err='Please fill all fields.';
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="card">
  <h2>Register</h2>
  <?php if($err):?><div class="error"><?=esc($err)?></div><?php endif;?>
  <form method="post">
    <label>Name</label><input name="name" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" required>
    <button type="submit">Create Account</button>
  </form>
  <p><a href="index.php">Back to Login</a></p>
</div>
</body></html>
