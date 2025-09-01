<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);
$message = "";

// Fetch existing product
$res = $conn->query("SELECT * FROM products WHERE product_id=$product_id");
$product = $res->fetch_assoc();
if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name']);
    $product_price = trim($_POST['product_price']);
    $stock         = intval($_POST['stock']);

    $product_image_path = $product['product_image'];

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg','jpeg','png','gif'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = 'product_' . time() . '.' . $fileExtension;
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Delete old image if exists
                if (!empty($product_image_path) && file_exists($product_image_path)) {
                    unlink($product_image_path);
                }
                $product_image_path = $destPath;
            } else {
                $message = "Failed to upload new image.";
            }
        } else {
            $message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE products SET product_name=?, product_price=?, stock=?, product_image=? WHERE product_id=?");
    $stmt->bind_param("sdisi", $product_name, $product_price, $stock, $product_image_path, $product_id);
    if ($stmt->execute()) {
        $message = "Product updated successfully!";
        // Refresh product data
        $product['product_name'] = $product_name;
        $product['product_price'] = $product_price;
        $product['stock'] = $stock;
        $product['product_image'] = $product_image_path;
    } else {
        $message = "Database update failed: ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>
<style>
body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width:500px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1); position:relative;}
h2 { text-align:center; color:#1e3799; margin-bottom:20px;}
input[type=text], input[type=number], input[type=file] { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button.update-btn { background:#1e3799; color:white; padding:12px; border:none; border-radius:5px; cursor:pointer; width:100%; font-size:16px; }
button.update-btn:hover { background:#40739e; }
.message { margin-bottom:15px; text-align:center; font-weight:bold; color:green; }
img { display:block; margin:10px auto; max-width:150px; border-radius:8px; }

/* Navigation Buttons */
.nav-buttons { display:flex; justify-content:space-between; margin-bottom:20px; }
.nav-buttons a { display:inline-block; padding:10px 20px; background:#44bd32; color:white; text-decoration:none; font-weight:bold; border-radius:8px; transition:0.3s; }
.nav-buttons a:hover { background:#2ecc71; }
</style>
</head>
<body>

<div class="container">
  <div class="nav-buttons">
    <a href="dashboard.php">Back to Dashboard</a>
    <a href="products.php">Back to Products</a>
  </div>

  <h2>Edit Product</h2>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="product_name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
    <input type="number" name="product_price" step="0.01" placeholder="Product Price" value="<?php echo $product['product_price']; ?>" required>
    <input type="number" name="stock" placeholder="Stock" value="<?php echo $product['stock']; ?>" required>

    <label>Current Image:</label>
    <?php if(!empty($product['product_image']) && file_exists($product['product_image'])): ?>
        <img src="<?php echo $product['product_image']; ?>" alt="Product Image">
    <?php else: ?>
        <img src="uploads/products/default.png" alt="Default Image">
    <?php endif; ?>

    <input type="file" name="product_image" accept="image/*">
    <button type="submit" class="update-btn">Update Product</button>
  </form>
</div>

</body>
</html>
