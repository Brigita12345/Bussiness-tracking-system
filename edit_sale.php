<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sales_id = intval($_GET['id']);
$message = "";

// Fetch the sale
$stmt = $conn->prepare("SELECT s.*, p.product_name FROM sales s JOIN products p ON s.product_id=p.product_id WHERE s.sales_id=? AND s.id=?");
$stmt->bind_param("ii", $sales_id, $_SESSION['saler_id']);
$stmt->execute();
$res = $stmt->get_result();
$sale = $res->fetch_assoc();

if (!$sale) {
    header("Location: sales.php");
    exit();
}

// Fetch all products for dropdown
$products_res = $conn->query("SELECT * FROM products ORDER BY product_name ASC");
$products = $products_res->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $update_stmt = $conn->prepare("UPDATE sales SET product_id=?, quantity=? WHERE sales_id=? AND id=?");
    $update_stmt->bind_param("iiii", $product_id, $quantity, $sales_id, $_SESSION['saler_id']);
    
    if ($update_stmt->execute()) {
        $message = "Sale updated successfully!";
        // Refresh sale data
        $stmt = $conn->prepare("SELECT s.*, p.product_name FROM sales s JOIN products p ON s.product_id=p.product_id WHERE s.sales_id=? AND s.id=?");
        $stmt->bind_param("ii", $sales_id, $_SESSION['saler_id']);
        $stmt->execute();
        $sale = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Sale</title>
<style>
body { font-family:"Segoe UI", sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:500px; margin:50px auto; background:white; padding:30px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#1e3799; margin-bottom:20px;}
input, select { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button.update-btn { background:#1e3799; color:white; padding:12px; border:none; border-radius:5px; cursor:pointer; width:100%; font-size:16px; }
button.update-btn:hover { background:#40739e; }
.message { text-align:center; margin-bottom:15px; font-weight:bold; color:green; }
.back-btn { display:inline-block; margin-top:15px; padding:10px 15px; background:#40739e; color:white; text-decoration:none; border-radius:5px; }
.back-btn:hover { background:#1e3799; }
</style>
</head>
<body>

<div class="container">
  <h2>Edit Sale</h2>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Product:</label>
    <select name="product_id" required>
      <?php foreach($products as $p): ?>
        <option value="<?php echo $p['product_id']; ?>" <?php if($p['product_id']==$sale['product_id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($p['product_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Quantity:</label>
    <input type="number" name="quantity" value="<?php echo $sale['quantity']; ?>" min="1" required>

    <button type="submit" class="update-btn">Update Sale</button>
  </form>

  <a href="sales.php" class="back-btn">← Back to Sales</a>
</div>

</body>
</html>
