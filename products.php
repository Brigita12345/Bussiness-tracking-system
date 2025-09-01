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
    
    // Optionally, delete the product image file
    $res = $conn->query("SELECT product_image FROM products WHERE product_id=$delete_id");
    $prod = $res->fetch_assoc();
    if(!empty($prod['product_image']) && file_exists($prod['product_image'])) {
        unlink($prod['product_image']);
    }

    $conn->query("DELETE FROM products WHERE product_id=$delete_id");
    header("Location: products.php");
    exit();
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY product_id DESC";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Products</title>
<style>
body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:1200px; margin:50px auto; padding:0 20px; position:relative; }
h2 { text-align:center; color:#1e3799; margin-bottom:30px; }

/* Fixed Back Button */
.back-btn { position:fixed; top:20px; right:20px; padding:12px 25px; background:#44bd32; color:white; text-decoration:none; font-weight:bold; border-radius:8px; transition:0.3s; z-index:999; box-shadow:0 4px 8px rgba(0,0,0,0.2);}
.back-btn:hover { background:#2ecc71; }

/* Floating Add Product Button */
.add-btn { position:fixed; bottom:30px; right:30px; background:#1e3799; color:white; padding:15px 25px; font-weight:bold; border-radius:50px; text-decoration:none; box-shadow:0 4px 12px rgba(0,0,0,0.3); transition:0.3s; z-index:999; }
.add-btn:hover { background:#40739e; }

/* Product Cards */
.cards { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:20px; margin-top:20px;}
.card { background:white; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1); overflow:hidden; transition:0.3s; position:relative; }
.card:hover { transform: translateY(-5px); }
.card img { width:100%; height:180px; object-fit:cover; }
.card-content { padding:15px; text-align:center; }
.card-content h3 { margin:10px 0 5px 0; font-size:18px; color:#1e3799; }
.card-content p { margin:5px 0; font-size:16px; color:#333; }
.card-content span { display:block; margin-top:8px; font-weight:bold; color:#44bd32; font-size:16px; }

/* Buttons inside cards */
.card-content .btn { margin-top:8px; padding:8px 12px; border:none; border-radius:5px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; }
.edit-btn { background:#fbc531; color:#fff; }
.edit-btn:hover { background:#e1b12c; }
.delete-btn { background:#e84118; color:#fff; }
.delete-btn:hover { background:#c23616; }
</style>
</head>
<body>

<div class="container">
  <h2>All Products</h2>
  <!-- Back to Dashboard -->
  <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
  <!-- Add Product -->
  <a href="add_items.php" class="add-btn">+ Add Product</a>

  <div class="cards">
    <?php if(count($products) > 0): ?>
        <?php foreach($products as $prod): ?>
          <div class="card">
            <?php 
              $imgPath = "uploads/products/default.png";
              if(!empty($prod['product_image']) && file_exists($prod['product_image'])){
                $imgPath = $prod['product_image'];
              }
            ?>
            <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($prod['product_name']); ?>">
            <div class="card-content">
              <h3><?php echo htmlspecialchars($prod['product_name']); ?></h3>
              <p>Price: $<?php echo number_format($prod['product_price'], 2); ?></p>
              <span>Stock: <?php echo $prod['stock']; ?></span>
              
              <!-- Edit & Delete -->
              <a class="btn edit-btn" href="edit_product.php?id=<?php echo $prod['product_id']; ?>">Edit</a>
              <a class="btn delete-btn" href="products.php?delete_id=<?php echo $prod['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; font-size:18px; color:#555;">No products added yet.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
