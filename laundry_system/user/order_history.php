<?php
session_start();
require '../db_connect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$uid = $_SESSION['user_id'];

// ✅ Fetch all orders by this user
$orders = $conn->query("
    SELECT * FROM orders 
    WHERE user_id = $uid 
    ORDER BY created_at DESC
");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Order History</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.table th { background: #3498db; color: white; }
.card { max-width: 1000px; margin: 30px auto; padding: 20px; background: #f8f8f8; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
.status-paid { color: green; font-weight: bold; }
.status-unpaid { color: red; font-weight: bold; }
.status-partial { color: orange; font-weight: bold; }
a.track-link { color: #3498db; text-decoration: none; font-weight: bold; }
a.track-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>My Orders</h2>

  <!-- ✅ Completed Orders -->
  <h3>🟢 Completed Orders (Delivered/Pickup & Paid)</h3>
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Services</th>
      <th>Mode</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Created</th>
      <th>Action</th>
    </tr>
    <?php 
    $hasCompleted = false;
    $orders->data_seek(0);
    while ($o = $orders->fetch_assoc()):
      if (($o['status'] == 'Delivered' || $o['status'] == 'Pickup') && $o['payment_status'] == 'Paid'):
        $hasCompleted = true;
    ?>
    <tr>
      <td><?= esc($o['id']) ?></td>
      <td><?= esc($o['options']) ?></td>
      <td><?= esc($o['service_mode']) ?></td>
      <td><?= esc($o['payment_method']) ?></td>
      <td><?= number_format($o['total_cost'], 2) ?></td>
      <td><?= esc($o['status']) ?></td>
      <td class="status-paid"><?= esc($o['payment_status']) ?></td>
      <td><?= esc($o['created_at']) ?></td>
      <td><a class="track-link" href="order_status.php?id=<?= esc($o['id']) ?>">View</a></td>
    </tr>
    <?php endif; endwhile; ?>
    <?php if (!$hasCompleted): ?>
    <tr><td colspan="9">No completed orders yet.</td></tr>
    <?php endif; ?>
  </table>

  <!-- ✅ Active Orders -->
  <h3>🟡 Active Orders (Pending / In Progress / Ready / Unpaid)</h3>
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Services</th>
      <th>Mode</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Created</th>
      <th>Action</th>
    </tr>
    <?php 
    $hasActive = false;
    $orders->data_seek(0);
    while ($o = $orders->fetch_assoc()):
      if (!(($o['status'] == 'Delivered' || $o['status'] == 'Pickup') && $o['payment_status'] == 'Paid')):
        $hasActive = true;
    ?>
    <tr>
      <td><?= esc($o['id']) ?></td>
      <td><?= esc($o['options']) ?></td>
      <td><?= esc($o['service_mode']) ?></td>
      <td><?= esc($o['payment_method']) ?></td>
      <td><?= number_format($o['total_cost'], 2) ?></td>
      <td><?= esc($o['status']) ?></td>
      <td class="<?= 
        $o['payment_status']=='Paid' ? 'status-paid' : 
        ($o['payment_status']=='Partial' ? 'status-partial' : 'status-unpaid')
      ?>"><?= esc($o['payment_status']) ?></td>
      <td><?= esc($o['created_at']) ?></td>
      <td><a class="track-link" href="order_status.php?id=<?= esc($o['id']) ?>">Track</a></td>
    </tr>
    <?php endif; endwhile; ?>
    <?php if (!$hasActive): ?>
    <tr><td colspan="9">No active orders right now.</td></tr>
    <?php endif; ?>
  </table>
</div>
</body>
</html>
