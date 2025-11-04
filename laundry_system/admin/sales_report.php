<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin') header('Location: ../index.php');

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$stmt = $conn->prepare('SELECT DATE(created_at) as d, COUNT(*) as orders, IFNULL(SUM(total_cost),0) as revenue FROM orders WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC');
$stmt->bind_param('ss',$from,$to);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Sales Report</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include '../includes/header.php'; ?>
<div class="card">
  <h2>Sales Report</h2>
  <form method="get">
    <label>From</label><input type="date" name="from" value="<?=esc($from)?>">
    <label>To</label><input type="date" name="to" value="<?=esc($to)?>">
    <button type="submit">Filter</button>
  </form>
  <table class="table"><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr>
  <?php foreach($data as $r): ?>
    <tr><td><?=esc($r['d'])?></td><td><?=esc($r['orders'])?></td><td><?=number_format($r['revenue'],2)?></td></tr>
  <?php endforeach; ?>
  </table>
</div></body></html>
