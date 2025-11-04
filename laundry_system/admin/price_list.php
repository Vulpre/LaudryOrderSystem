<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$err = '';
$success = '';

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price_per_kilo = floatval($_POST['price_per_kilo']);
    $price_per_item = floatval($_POST['price_per_item']);
    $express_fee = floatval($_POST['express_fee']);

    $stmt = $conn->prepare("INSERT INTO price_list (price_per_kilo, price_per_item, express_fee, updated_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('ddd', $price_per_kilo, $price_per_item, $express_fee);

    if ($stmt->execute()) {
        $success = "Price list updated successfully!";
    } else {
        $err = "Failed to update price list: " . $stmt->error;
    }
}

// ✅ Fetch latest prices
$current = $conn->query("SELECT * FROM price_list ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Price List</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.card { max-width: 600px; margin: 40px auto; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
label { display: block; margin-top: 10px; }
input[type="number"] { width: 100%; padding: 8px; margin-top: 5px; }
button { margin-top: 15px; padding: 10px 15px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #2980b9; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>Manage Price List</h2>

  <?php if ($err): ?><p class="error"><?= htmlspecialchars($err) ?></p><?php endif; ?>
  <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

  <form method="post">
    <label>Price per Kilo (₱)</label>
    <input type="number" name="price_per_kilo" step="0.01" value="<?= htmlspecialchars($current['price_per_kilo'] ?? 0) ?>" required>

    <label>Price per Item (₱)</label>
    <input type="number" name="price_per_item" step="0.01" value="<?= htmlspecialchars($current['price_per_item'] ?? 0) ?>" required>

    <label>Express Service Fee (₱)</label>
    <input type="number" name="express_fee" step="0.01" value="<?= htmlspecialchars($current['express_fee'] ?? 0) ?>" required>

    <button type="submit">Update Prices</button>
  </form>

  <?php if ($current): ?>
  <hr>
  <h3>Current Prices</h3>
  <p><strong>Per Kilo:</strong> ₱<?= number_format($current['price_per_kilo'], 2) ?></p>
  <p><strong>Per Item:</strong> ₱<?= number_format($current['price_per_item'], 2) ?></p>
  <p><strong>Express Fee:</strong> ₱<?= number_format($current['express_fee'], 2) ?></p>
  <p><em>Last Updated: <?= htmlspecialchars($current['updated_at']) ?></em></p>
  <?php endif; ?>
</div>

</body>
</html>
