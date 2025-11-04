<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// ✅ Move completed orders automatically
$conn->query("
  INSERT INTO order_history (id, user_id, service_type, service_mode, options, pickup_option, payment_method, weight, total_cost, status, payment_status, created_at)
  SELECT id, user_id, service_type, service_mode, options, pickup_option, payment_method, weight, total_cost, status, payment_status, created_at
  FROM orders
  WHERE (status IN ('Delivered', 'Pickup') AND payment_status = 'Paid')
  AND id NOT IN (SELECT id FROM order_history)
");

$conn->query("
  DELETE FROM orders 
  WHERE status IN ('Delivered', 'Pickup') AND payment_status = 'Paid'
");

// ✅ Update Order Status or Payment Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid = intval($_POST['order_id']);

    if (isset($_POST['update_status'])) {
        $status = $_POST['status'];
        $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
        $stmt->bind_param('si', $status, $oid);
        $stmt->execute();
    }

    if (isset($_POST['update_payment'])) {
        $payment_status = $_POST['payment_status'];
        $stmt = $conn->prepare('UPDATE orders SET payment_status=? WHERE id=?');
        $stmt->bind_param('si', $payment_status, $oid);
        $stmt->execute();
    }

    // Move again in case this update made it "complete"
    $conn->query("
      INSERT INTO order_history (id, user_id, service_type, service_mode, options, pickup_option, payment_method, weight, total_cost, status, payment_status, created_at)
      SELECT id, user_id, service_type, service_mode, options, pickup_option, payment_method, weight, total_cost, status, payment_status, created_at
      FROM orders
      WHERE (status IN ('Delivered', 'Pickup') AND payment_status = 'Paid')
      AND id NOT IN (SELECT id FROM order_history)
    ");

    $conn->query("
      DELETE FROM orders 
      WHERE status IN ('Delivered', 'Pickup') AND payment_status = 'Paid'
    ");
}

// ✅ Get remaining active orders
$orders = $conn->query("
    SELECT o.*, u.name AS customer
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Orders</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.table th { background: #3498db; color: white; }
button { padding: 5px 10px; cursor: pointer; border: none; border-radius: 5px; background: #2ecc71; color: white; }
button:hover { background: #27ae60; }
select { padding: 4px; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>Active Orders</h2>
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Customer</th>
      <th>Payment Method</th>
      <th>Total</th>
      <th>Order Status</th>
      <th>Payment Status</th>
      <th>Created</th>
    </tr>

    <?php while ($o = $orders->fetch_assoc()): ?>
    <tr>
      <td><?= esc($o['id']) ?></td>
      <td><?= esc($o['customer']) ?></td>
      <td><?= esc($o['payment_method']) ?></td>
      <td>₱<?= number_format($o['total_cost'], 2) ?></td>

      <!-- Order Status -->
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="order_id" value="<?= esc($o['id']) ?>">
          <select name="status">
            <option <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option <?= $o['status']=='In Progress'?'selected':'' ?>>In Progress</option>
            <option <?= $o['status']=='Ready'?'selected':'' ?>>Ready</option>
            <option <?= $o['status']=='Delivered'?'selected':'' ?>>Delivered</option>
            <option <?= $o['status']=='Pickup'?'selected':'' ?>>Pickup</option>
          </select>
          <button name="update_status" type="submit">Update</button>
        </form>
      </td>

      <!-- Payment Status -->
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="order_id" value="<?= esc($o['id']) ?>">
          <select name="payment_status">
            <option value="Unpaid"  <?= $o['payment_status']=='Unpaid'?'selected':'' ?>>Unpaid</option>
            <option value="Partial" <?= $o['payment_status']=='Partial'?'selected':'' ?>>Partial</option>
            <option value="Paid"    <?= $o['payment_status']=='Paid'?'selected':'' ?>>Paid</option>
          </select>
          <button name="update_payment" type="submit">Update</button>
        </form>
      </td>

      <td><?= esc($o['created_at']) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>
