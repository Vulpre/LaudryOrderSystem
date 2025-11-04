<?php
session_start();
require '../db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$uid = $_SESSION['user_id'];

// ✅ Get all active and completed orders for this user
$query = "
    SELECT id, options, service_mode, total_cost, status, payment_status, payment_method, created_at
    FROM orders
    WHERE user_id = $uid
    ORDER BY created_at DESC
";
$orders = $conn->query($query);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Track My Orders</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body { background: #f4f6f8; font-family: Arial, sans-serif; }
.card {
  max-width: 950px; margin: 40px auto; background: #fff; padding: 25px;
  border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 14px; }
.table th { background: #3498db; color: white; }
.status-step {
  display: flex; justify-content: center; align-items: center; gap: 25px; margin-top: 10px;
}
.status-dot {
  width: 20px; height: 20px; border-radius: 50%; background: #ccc;
  transition: background 0.3s, box-shadow 0.3s;
}
.status-dot.active {
  background: #3498db; box-shadow: 0 0 8px #3498db;
}
.status-label { font-size: 12px; text-align: center; margin-top: 5px; }
.status-paid { color: green; font-weight: bold; }
.status-unpaid { color: red; font-weight: bold; }
.status-partial { color: orange; font-weight: bold; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>📦 Track My Orders</h2>
  <p style="color:#555;">Monitor your laundry’s progress and payment updates below.</p>

  <?php if ($orders->num_rows > 0): ?>
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Services</th>
      <th>Mode</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Order Status</th>
      <th>Payment</th>
      <th>Progress</th>
    </tr>
    <?php while ($o = $orders->fetch_assoc()): ?>
      <tr>
        <td><?= esc($o['id']) ?></td>
        <td><?= esc($o['options']) ?></td>
        <td><?= esc($o['service_mode']) ?></td>
        <td><?= esc($o['payment_method']) ?></td>
        <td>₱<?= number_format($o['total_cost'], 2) ?></td>
        <td><?= esc($o['status']) ?></td>
        <td class="<?=
          $o['payment_status']=='Paid' ? 'status-paid' :
          ($o['payment_status']=='Partial' ? 'status-partial' : 'status-unpaid')
        ?>">
          <?= esc($o['payment_status']) ?>
        </td>
        <td>
          <div class="status-step">
            <?php
            $statuses = ['Pending', 'In Progress', 'Ready', 'Delivered', 'Pickup'];
            foreach ($statuses as $s):
              $active = array_search($o['status'], $statuses) >= array_search($s, $statuses);
            ?>
              <div>
                <div class="status-dot <?= $active ? 'active' : '' ?>"></div>
                <div class="status-label"><?= $s ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
  <?php else: ?>
    <p>No orders found yet.</p>
  <?php endif; ?>
</div>
</body>
</html>
