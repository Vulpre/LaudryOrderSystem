<?php
session_start();
require 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    else header('Location: user/home.php');
    exit;
}

$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT id,name,password,role FROM users WHERE email=?');
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        // passwords stored as SHA2(...) in init.sql example
        if (hash('sha256',$pass) === $row['password']) {
            $_SESSION['user_id']=$row['id'];
            $_SESSION['name']=$row['name'];
            $_SESSION['role']=$row['role'];
            if ($row['role']==='admin') header('Location: admin/dashboard.php'); else header('Location: user/home.php');
            exit;
        }
    }
    $err = 'Invalid credentials';
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login - Laundry</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="card">
  <h2>Login</h2>
  <?php if($err):?><div class="error"><?=esc($err)?></div><?php endif;?>
  <form method="post">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
  </form>
  <p>Don't have account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
