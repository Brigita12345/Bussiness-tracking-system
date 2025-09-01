<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM sales WHERE sales_id=$delete_id");
    header("Location: sales.php");
    exit();
}

// Fetch all sales
$sql = "SELECT s.*, p.product_name, p.product_price
        FROM sales s 
        JOIN products p ON s.product_id = p.product_id
        WHERE s.id = ?
        ORDER BY s.sales_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['saler_id']);
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_sales_qty = 0;
$total_revenue = 0;
foreach ($sales as $s) {
    $total_sales_qty += $s['quantity'];
    $total_revenue += $s['quantity'] * $s['product_price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Dashboard</title>
<style>
body { font-family:"Segoe UI", sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:1200px; margin:50px auto; padding:0 20px;}
h2 { text-align:center; color:#1e3799; margin-bottom:20px;}
.cards { display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
.card { flex:1; background:white; padding:20px; border-radius:12px; text-align:center; font-weight:bold; color:#1e3799; box-shadow:0 4px 8px rgba(0,0,0,0.1); }
.card span { display:block; font-size:22px; color:#44bd32; margin-top:8px; }
.add-btn { display:inline-block; margin-bottom:15px; padding:10px 20px; background:#1e3799; color:white; text-decoration:none; border-radius:5px; }
.add-btn:hover { background:#40739e; }
table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
th, td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:center;}
th { background:#1e3799; color:white;}
tr:hover { background:#f1f2f6; }
.edit-btn { background:#fbc531; color:white; padding:6px 10px; border-radius:5px; text-decoration:none;}
.edit-btn:hover { background:#e1b12c; }
.delete-btn { background:#e84118; color:white; padding:6px 10px; border-radius:5px; text-decoration:none;}
.delete-btn:hover { background:#c23616; }
.back-btn { display:inline-block; margin-bottom:20px; padding:10px 15px; background:#40739e; color:white; text-decoration:none; border-radius:5px;}
.back-btn:hover { background:#1e3799;}
</style>
</head>
<body>

<div class="container">
  <h2>Sales Dashboard</h2>
  
  <!-- Cards for totals -->
  <div class="cards">
    <div class="card">Total Products Sold <span><?php echo $total_sales_qty; ?></span></div>
    <div class="card">Total Revenue ($) <span><?php echo number_format($total_revenue,2); ?></span></div>
  </div>

  <a href="add_sale.php" class="add-btn">+ Add New Sale</a>

  <!-- Sales Table -->
  <table>
    <tr>
      <th>#</th>
      <th>Product Name</th>
      <th>Quantity</th>
      <th>Price ($)</th>
      <th>Total ($)</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
    <?php if(count($sales) > 0): ?>
      <?php foreach($sales as $index => $s): ?>
      <tr>
        <td><?php echo $index+1; ?></td>
        <td><?php echo htmlspecialchars($s['product_name']); ?></td>
        <td><?php echo $s['quantity']; ?></td>
        <td><?php echo number_format($s['product_price'],2); ?></td>
        <td><?php echo number_format($s['quantity'] * $s['product_price'],2); ?></td>
        <td><?php echo date("Y-m-d H:i", strtotime($s['sales_date'])); ?></td>
        <td>
          <a class="edit-btn" href="edit_sale.php?id=<?php echo $s['sales_id']; ?>">Edit</a>
          <a class="delete-btn" href="sales.php?delete_id=<?php echo $s['sales_id']; ?>" onclick="return confirm('Are you sure to delete this sale?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="7" style="text-align:center;">No sales recorded yet.</td></tr>
    <?php endif; ?>
  </table>

  <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
