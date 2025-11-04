<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id'])) header('Location: ../index.php');
?>
<!doctype html><html><head><meta charset="utf-8"><title>User Home</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include '../includes/header.php'; ?>
<div class="card">
  <h2>Welcome, <?=htmlspecialchars($_SESSION['name'])?></h2>
  <p class="small">Create new orders, track status, and view receipts.</p>
  <a href="create_order.php"><button>Create New Order</button></a>
  <a href="order_history.php"><button style="margin-top:8px">Order History</button></a>
</div></body></html>
