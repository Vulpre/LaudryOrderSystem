<?php
session_start();
require '../db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$prices = $conn->query('SELECT * FROM price_list ORDER BY id DESC LIMIT 1')->fetch_assoc();
$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['service_mode'];
    $weight = floatval($_POST['weight'] ?? 0);
    $items = intval($_POST['items'] ?? 0);
    $options = implode(',', $_POST['services'] ?? []);
    $express = isset($_POST['express']) ? 1 : 0;
    $pickup_option = $_POST['pickup_option'] ?? 'Delivery';
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';

    // ✅ Calculate total cost
    $total = 0;
    if ($mode === 'per_kilo') $total += $weight * floatval($prices['price_per_kilo']);
    else $total += $items * floatval($prices['price_per_item']);
    if ($express) $total += floatval($prices['express_fee']);

    $service_type = $express ? 'Express' : 'Regular';
    $uid = $_SESSION['user_id'];

    // ✅ Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, service_type, service_mode, options, pickup_option, payment_method, weight, total_cost)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'isssssdd',
        $uid,
        $service_type,
        $mode,
        $options,
        $pickup_option,
        $payment_method,
        $weight,
        $total
    );

    if ($stmt->execute()) {
        $success = "Order created successfully. Total: ₱" . number_format($total, 2);
    } else {
        $err = "Failed to create order: " . $stmt->error;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Create Order</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body {
  background: #f4f6f8;
  font-family: Arial, sans-serif;
}

.card {
  max-width: 650px;
  margin: 40px auto;
  padding: 25px 30px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

h2 {
  text-align: center;
  margin-bottom: 15px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

label {
  font-weight: bold;
}

select, input[type="number"], button {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
}

button {
  background: #3498db;
  color: white;
  font-weight: bold;
  cursor: pointer;
  padding: 10px;
  border: none;
  border-radius: 6px;
}

button:hover {
  background: #2980b9;
}

.success {
  color: green;
  font-weight: bold;
  margin-bottom: 10px;
}

.error {
  color: red;
  font-weight: bold;
  margin-bottom: 10px;
}

/* ✅ Align checkboxes horizontally */
.services-group {
  display: flex;
  flex-wrap: wrap;
  gap: 15px 30px;
  margin-top: 8px;
}

.services-group label {
  font-weight: normal;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Responsive */
@media (max-width: 600px) {
  .services-group {
    flex-direction: column;
    gap: 8px;
  }
}

.hidden {
  display: none;
}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="card">
  <h2>Create New Laundry Order</h2>
  <?php if($err): ?><div class="error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <form method="post">
    <label>Service Mode</label>
    <select name="service_mode" id="service_mode" required>
      <option value="per_kilo">Per Kilo</option>
      <option value="per_piece">Per Piece</option>
    </select>

    <label>Services</label>
    <div class="services-group">
      <label><input type="checkbox" name="services[]" value="Wash"> Wash</label>
      <label><input type="checkbox" name="services[]" value="Dry"> Dry</label>
      <label><input type="checkbox" name="services[]" value="Fold"> Fold</label>
      <label><input type="checkbox" name="services[]" value="Iron"> Iron</label>
    </div>

    <!-- ✅ Weight only for per kilo -->
    <div id="weight_field">
      <label>Weight (kg)</label>
      <input name="weight" step="0.01" type="number" value="0">
    </div>

    <!-- ✅ Items only for per piece -->
    <div id="items_field" class="hidden">
      <label>Items (pieces)</label>
      <input name="items" type="number" value="0">
    </div>

    <label><input type="checkbox" name="express" value="1"> Express Service (extra fee)</label>

    <label>Pickup Option</label>
    <select name="pickup_option" id="pickup_option" required>
      <option value="Delivery">Delivery</option>
      <option value="Pickup">Pickup</option>
    </select>

    <label>Payment Method</label>
    <select name="payment_method" id="payment_method" required>
      <option value="Cash on Delivery">Cash on Delivery</option>
      <option value="Via App">Via App</option>
    </select>

    <button type="submit">Place Order</button>
  </form>
</div>

<script>
// ✅ Show only Weight or Items depending on service mode
document.getElementById('service_mode').addEventListener('change', function() {
  const weightField = document.getElementById('weight_field');
  const itemsField = document.getElementById('items_field');

  if (this.value === 'per_kilo') {
    weightField.classList.remove('hidden');
    itemsField.classList.add('hidden');
  } else {
    itemsField.classList.remove('hidden');
    weightField.classList.add('hidden');
  }
});

// ✅ Hide "Cash on Delivery" if Pickup is selected
document.getElementById('pickup_option').addEventListener('change', function() {
  const paymentSelect = document.getElementById('payment_method');
  const selectedPickup = this.value;

  paymentSelect.innerHTML = '';

  if (selectedPickup === 'Pickup') {
    const viaApp = document.createElement('option');
    viaApp.value = 'Via App';
    viaApp.textContent = 'Via App';
    paymentSelect.appendChild(viaApp);
  } else {
    const cod = document.createElement('option');
    cod.value = 'Cash on Delivery';
    cod.textContent = 'Cash on Delivery';
    const viaApp = document.createElement('option');
    viaApp.value = 'Via App';
    viaApp.textContent = 'Via App';
    paymentSelect.appendChild(cod);
    paymentSelect.appendChild(viaApp);
  }
});
</script>
</body>
</html>
