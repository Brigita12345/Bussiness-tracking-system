<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch products for dropdown
$productsRes = $conn->query("SELECT * FROM products ORDER BY product_name ASC");
$products = $productsRes->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);
    $sales_date = date("Y-m-d H:i:s"); // current datetime

    if ($quantity <= 0) {
        $message = "Quantity must be greater than zero.";
    } else {
        $stmt = $conn->prepare("INSERT INTO sales (product_id, id, quantity, sales_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $_SESSION['saler_id'], $quantity, $sales_date);
        if ($stmt->execute()) {
            $message = "Sale added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Sale</title>
<style>
body { font-family:"Segoe UI", sans-serif; background:#f5f6fa; margin:0; padding:0; }
.container { max-width:500px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#1e3799; margin-bottom:20px; }
input, select { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button.add-btn { width:100%; padding:12px; border:none; border-radius:5px; background:#1e3799; color:#fff; font-size:16px; cursor:pointer;}
button.add-btn:hover { background:#40739e; }
.message { text-align:center; font-weight:bold; margin-bottom:15px; color:green; }
.back-btn { display:inline-block; margin-bottom:20px; padding:10px 15px; background:#1e3799; color:#fff; text-decoration:none; border-radius:5px; }
.back-btn:hover { background:#40739e; }
</style>
</head>
<body>

<div class="container">
  <h2>Add Sale</h2>
  <a href="sales.php" class="back-btn">← Back to Sales</a>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Product</label>
    <select name="product_id" required>
      <option value="">Select a product</option>
      <?php foreach($products as $prod): ?>
        <option value="<?php echo $prod['product_id']; ?>"><?php echo htmlspecialchars($prod['product_name']); ?> ($<?php echo number_format($prod['product_price'],2); ?>)</option>
      <?php endforeach; ?>
    </select>

    <label>Quantity Sold</label>
    <input type="number" name="quantity" min="1" required>

    <button type="submit" class="add-btn">Add Sale</button>
  </form>
</div>

</body>
</html>
