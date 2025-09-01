<?php
session_start();
include("connection.php");

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Get order ID from query
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: order.php");
    exit();
}
$order_id = intval($_GET['id']);

// Fetch products for dropdown
$products = $conn->query("SELECT * FROM products ORDER BY product_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch existing order
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id     = intval($_POST['product_id']);
    $quantity       = intval($_POST['quantity']);
    $quality        = trim($_POST['quality']);
    $buying_price   = floatval($_POST['buying_price']);
    $transport_fee  = floatval($_POST['transport_fee']);
    $total_cost     = ($quantity * $buying_price) + $transport_fee;

    $stmt = $conn->prepare("UPDATE orders 
                            SET product_id=?, quantity=?, quality=?, buying_price=?, transport_fee=?, total_cost=? 
                            WHERE order_id=?");
    $stmt->bind_param("iisdddi", $product_id, $quantity, $quality, $buying_price, $transport_fee, $total_cost, $order_id);

    if ($stmt->execute()) {
        $message = "Order updated successfully!";
        // Refresh order details
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "Error updating order: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Order</title>
<style>
body { font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:700px; margin:50px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#1e3799; margin-bottom:25px;}
form { display:grid; grid-template-columns: 1fr 1fr; gap:15px; }
form input, form select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; }
form input[type="number"] { -moz-appearance:textfield; }
form input[type=number]::-webkit-inner-spin-button,
form input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
button.update-btn { grid-column: span 2; background:#44bd32; color:#fff; padding:12px; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button.update-btn:hover { background:#2ecc71; }
.message { grid-column: span 2; text-align:center; font-weight:bold; color:green; margin-bottom:10px; }

/* Back button */
.back-btn { display:inline-block; margin-bottom:20px; padding:10px 20px; background:#1e3799; color:#fff; text-decoration:none; border-radius:6px; }
.back-btn:hover { background:#40739e; }
</style>
<script>
function calculateTotal() {
    let quantity = parseFloat(document.getElementById('quantity').value) || 0;
    let price = parseFloat(document.getElementById('buying_price').value) || 0;
    let transport = parseFloat(document.getElementById('transport_fee').value) || 0;
    document.getElementById('total_cost').value = (quantity * price + transport).toFixed(2);
}
</script>
</head>
<body>
<div class="container">
  <a href="order.php" class="back-btn">← Back to Orders</a>
  <h2>Edit Order</h2>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST" oninput="calculateTotal()">
    <select name="product_id" required>
      <option value="">Select Product</option>
      <?php foreach($products as $p): ?>
        <option value="<?php echo $p['product_id']; ?>" 
          <?php if($order['product_id'] == $p['product_id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($p['product_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="number" id="quantity" name="quantity" placeholder="Quantity" min="1" required value="<?php echo $order['quantity']; ?>">

    <input type="text" name="quality" placeholder="Quality (optional)" value="<?php echo htmlspecialchars($order['quality']); ?>">
    <input type="number" id="buying_price" name="buying_price" placeholder="Buying Price per Unit" step="0.01" min="0" required value="<?php echo $order['buying_price']; ?>">

    <input type="number" id="transport_fee" name="transport_fee" placeholder="Transportation Fee" step="0.01" min="0" value="<?php echo $order['transport_fee']; ?>">
    <input type="text" id="total_cost" placeholder="Total Cost" readonly style="background:#e1e1e1;" value="<?php echo $order['total_cost']; ?>">

    <button type="submit" class="update-btn">Update Order</button>
  </form>
</div>
</body>
</html>
