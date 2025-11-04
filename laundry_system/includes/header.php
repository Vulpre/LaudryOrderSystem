<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// Helper for navigation links
function navLink($url, $label) {
  echo '<a href="http://localhost/laundry_system/' . $url . '">' . htmlspecialchars($label) . '</a>';
}
?>
<div class="nav">
  <strong>Hi, <?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest' ?></strong>

  <?php if (isset($_SESSION['user_id'])): ?>

    <?php if ($_SESSION['role'] === 'admin'): ?>
      <!-- ✅ Admin Navigation -->
      <?php navLink('admin/dashboard.php', 'Dashboard'); ?>
      <?php navLink('admin/manage_orders.php', 'Orders'); ?>
      <?php navLink('admin/order_history.php', 'History'); ?>
      <?php navLink('admin/price_list.php', 'Price List'); ?>
      <?php navLink('admin/sales_report.php', 'Sales Report'); ?>

    <?php else: ?>
      <!-- ✅ User Navigation -->
      <?php navLink('user/home.php', 'Home'); ?>
      <?php navLink('user/create_order.php', 'New Order'); ?>
      <?php navLink('user/order_status.php', 'Track Order'); ?>
      <?php navLink('user/order_history.php', 'History'); ?>
    <?php endif; ?>

    <a href="http://localhost/laundry_system/logout.php">Logout</a>

  <?php else: ?>
    <a href="http://localhost/laundry_system/index.php">Login</a>
  <?php endif; ?>
</div>
<hr>
