<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$uploaded_image = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name  = trim($_POST['product_name']);
    $product_price = trim($_POST['product_price']);
    $stock         = 1;

    $product_image_path = NULL;

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
                $product_image_path = $destPath;
                $uploaded_image = $destPath;
            } else {
                $message = "Failed to upload product image.";
            }
        } else {
            $message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        }
    } else {
        $message = "Please upload a product image.";
    }

    if ($product_image_path) {
        $sql = "INSERT INTO products (product_name, product_price, stock, product_image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdis", $product_name, $product_price, $stock, $product_image_path);
        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Database error: ".$conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<style>
body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f5f6fa; margin:0; padding:0;}
.container { max-width: 500px; margin: 50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#1e3799; margin-bottom:20px;}
input[type=text], input[type=number], input[type=file] { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button.add-btn, button.back-btn { padding:12px; border:none; border-radius:5px; cursor:pointer; width:100%; font-size:16px; margin-top:10px; }
button.add-btn { background:#1e3799; color:white; }
button.add-btn:hover { background:#40739e; }
button.back-btn { background:#44bd32; color:white; }
button.back-btn:hover { background:#2ecc71; }
.message { margin-bottom:15px; text-align:center; font-weight:bold; color:green; }
img.uploaded { display:block; margin:10px auto; max-width:200px; border-radius:8px; }
</style>
</head>
<body>

<div class="container">
  <h2>Add Product</h2>

  <?php if($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="product_name" placeholder="Product Name" required>
    <input type="number" name="product_price" step="0.01" placeholder="Product Price" required>
    <input type="file" name="product_image" accept="image/*" id="fileInput" required>
    <img id="preview" class="uploaded" style="display:none;" alt="Preview">
    <button type="submit" class="add-btn">Add Product</button>
  </form>

  <?php if($uploaded_image): ?>
    <h3 style="text-align:center; margin-top:20px;">Uploaded Image:</h3>
    <img src="<?php echo $uploaded_image; ?>" alt="Uploaded Product" class="uploaded">
  <?php endif; ?>

  <form action="dashboard.php">
    <button type="submit" class="back-btn">Back to Dashboard</button>
  </form>
</div>

<script>
// Live image preview
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('preview');

fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        preview.style.display = "block";
        const reader = new FileReader();
        reader.addEventListener('load', function() {
            preview.setAttribute('src', this.result);
        });
        reader.readAsDataURL(file);
    } else {
        preview.style.display = "none";
        preview.setAttribute('src', '');
    }
});
</script>

</body>
</html>
