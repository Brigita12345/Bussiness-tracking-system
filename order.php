<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id     = intval($_POST['product_id']);
    $quantity       = intval($_POST['quantity']);
    $quality        = trim($_POST['quality']);
    $buying_price   = floatval($_POST['buying_price']);
    $transport_fee  = floatval($_POST['transport_fee']);
    $total_cost     = ($quantity * $buying_price) + $transport_fee;

    $stmt = $conn->prepare("INSERT INTO orders (product_id, quantity, quality, buying_price, transport_fee, total_cost) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisddd", $product_id, $quantity, $quality, $buying_price, $transport_fee, $total_cost);
    if ($stmt->execute()) {
        $message = "Order added successfully!";
    } else {
        $message = "Database error: " . $conn->error;
    }
}

// Fetch products for dropdown
$products = $conn->query("SELECT * FROM products ORDER BY product_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch all orders
$orders = $conn->query("SELECT o.*, p.product_name 
                        FROM orders o 
                        JOIN products p ON o.product_id = p.product_id 
                        ORDER BY o.order_date DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Order</title>
<style>
body { font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:900px; margin:50px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#1e3799; margin-bottom:25px;}
form { display:grid; grid-template-columns: 1fr 1fr; gap:15px; }
form input, form select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; }
form input[type="number"] { -moz-appearance:textfield; }
form input[type=number]::-webkit-inner-spin-button,
form input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
button.add-btn { grid-column: span 2; background:#1e3799; color:#fff; padding:12px; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button.add-btn:hover { background:#40739e; }
.message { grid-column: span 2; text-align:center; font-weight:bold; color:green; margin-bottom:10px; }

/* Table */
table { width:100%; border-collapse:collapse; margin-top:30px; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#1e3799; color:white; }
tr:hover { background:#f1f1f1; }
.btn { padding:6px 10px; border:none; border-radius:5px; cursor:pointer; color:#fff; text-decoration:none; }
.delete-btn { background:#e84118; }
.delete-btn:hover { background:#c23616; }
.edit-btn { background:#fbc531; }
.edit-btn:hover { background:#e1b12c; }

/* Back button */
.back-btn { display:inline-block; margin-bottom:20px; padding:10px 20px; background:#44bd32; color:#fff; text-decoration:none; border-radius:6px; }
.back-btn:hover { background:#2ecc71; }
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
  <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
  <h2>Place New Order</h2>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST" oninput="calculateTotal()">
    <select name="product_id" required>
      <option value="">Select Product</option>
      <?php foreach($products as $p): ?>
        <option value="<?php echo $p['product_id']; ?>"><?php echo htmlspecialchars($p['product_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="number" id="quantity" name="quantity" placeholder="Quantity" min="1" required>

    <input type="text" name="quality" placeholder="Quality (optional)">
    <input type="number" id="buying_price" name="buying_price" placeholder="Buying Price per Unit" step="0.01" min="0" required>

    <input type="number" id="transport_fee" name="transport_fee" placeholder="Transportation Fee" step="0.01" min="0" value="0">
    <input type="text" id="total_cost" placeholder="Total Cost" readonly style="background:#e1e1e1;">

    <button type="submit" class="add-btn">Place Order</button>
  </form>

  <h2>Previous Orders</h2>
  <table>
    <tr>
      <th>Product</th>
      <th>Quantity</th>
      <th>Quality</th>
      <th>Buying Price</th>
      <th>Transport Fee</th>
      <th>Total Cost</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
    <?php if(count($orders) > 0): ?>
      <?php foreach($orders as $o): ?>
        <tr>
          <td><?php echo htmlspecialchars($o['product_name']); ?></td>
          <td><?php echo $o['quantity']; ?></td>
          <td><?php echo htmlspecialchars($o['quality']); ?></td>
          <td>$<?php echo number_format($o['buying_price'],2); ?></td>
          <td>$<?php echo number_format($o['transport_fee'],2); ?></td>
          <td>$<?php echo number_format($o['total_cost'],2); ?></td>
          <td><?php echo date('Y-m-d', strtotime($o['order_date'])); ?></td>
          <td>
            <a href="edit_order.php?id=<?php echo $o['order_id']; ?>" class="btn edit-btn">Edit</a>
            <a href="order.php?delete_id=<?php echo $o['order_id']; ?>" onclick="return confirm('Delete this order?');" class="btn delete-btn">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="8">No orders yet.</td></tr>
    <?php endif; ?>
  </table>
</div>
</body>
</html>
