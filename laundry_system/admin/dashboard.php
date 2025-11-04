<?php
session_start();
require '../db_connect.php';

// ✅ Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Initialize variables
$total_orders = $pending = $revenue = 0;

// ✅ Wrap database queries in checks to prevent fatal errors
try {
    // Check if orders table exists
    $check = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($check && $check->num_rows > 0) {
        // Count total orders
        $result = $conn->query("SELECT COUNT(*) AS c FROM orders");
        $total_orders = ($result) ? $result->fetch_assoc()['c'] : 0;

        // Count pending orders
        $result = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='Pending'");
        $pending = ($result) ? $result->fetch_assoc()['c'] : 0;

        // Calculate total revenue
        $result = $conn->query("SELECT IFNULL(SUM(total_cost), 0) AS s FROM orders");
        $revenue = ($result) ? $result->fetch_assoc()['s'] : 0;
    }
} catch (Exception $e) {
    // Show friendly error message for debugging
    $error_message = "Error loading dashboard: " . $e->getMessage();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.card {
  max-width: 500px;
  margin: 50px auto;
  padding: 20px;
  background: #f8f8f8;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  text-align: center;
}
button {
  padding: 8px 16px;
  border: none;
  background: #3498db;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}
button:hover {
  background: #2980b9;
}
.error {
  color: red;
  margin-bottom: 10px;
}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>Admin Dashboard</h2>

  <?php if (!empty($error_message)): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
  <?php else: ?>
    <p>Total Orders: <?= htmlspecialchars($total_orders) ?> |
       Pending: <?= htmlspecialchars($pending) ?> |
       Revenue: ₱<?= number_format($revenue, 2) ?></p>

    <a href="manage_orders.php"><button>Manage Orders</button></a>
    <a href="price_list.php"><button style="margin-top:8px">Edit Prices</button></a>
    <a href="sales_report.php"><button style="margin-top:8px">Sales Report</button></a>
  <?php endif; ?>
</div>

</body>
</html>
