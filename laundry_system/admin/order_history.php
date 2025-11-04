<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$history = $conn->query("
    SELECT h.*, u.name AS customer
    FROM order_history h
    JOIN users u ON h.user_id = u.id
    ORDER BY h.moved_at DESC
");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Order History</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.table th { background: #2c3e50; color: white; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="card">
  <h2>Completed Orders</h2>
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Customer</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Moved To History</th>
    </tr>
    <?php while ($o = $history->fetch_assoc()): ?>
    <tr>
      <td><?= esc($o['id']) ?></td>
      <td><?= esc($o['customer']) ?></td>
      <td><?= esc($o['payment_method']) ?></td>
      <td>₱<?= number_format($o['total_cost'], 2) ?></td>
      <td><?= esc($o['status']) ?></td>
      <td><?= esc($o['payment_status']) ?></td>
      <td><?= esc($o['moved_at']) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>
