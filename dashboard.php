<?php
session_start();
include("connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id    = $_SESSION['admin_id'];
$name  = $_SESSION['admin_name'];

// Fetch email + profile_pic
$sql = "SELECT email, profile_pic FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$email = $user['email'];
$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : "image/avatar.png";

/* =======================
   FETCH DASHBOARD STATS
   ======================= */
$total_products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0;
$total_sales    = $conn->query("SELECT COUNT(*) AS c FROM sales")->fetch_assoc()['c'] ?? 0;

$res = $conn->query("SELECT SUM(s.quantity * p.product_price) AS r 
                     FROM sales s JOIN products p ON s.product_id=p.product_id");
$total_revenue = $res->fetch_assoc()['r'] ?? 0;

$res = $conn->query("SELECT SUM(stock * product_price) AS e FROM products");
$total_expenses = $res->fetch_assoc()['e'] ?? 0;

$total_profit = $total_revenue - $total_expenses;
$total_loss   = ($total_profit < 0) ? abs($total_profit) : 0;
$total_discounts = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f6fa;
      height: 100vh;
      overflow-x: hidden;
    }
    /* Navbar */
    .navbar {
      background: #1e3799;
      color: white;
      padding: 15px 20px;
      display: flex;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .menu-btn {
      font-size: 22px;
      cursor: pointer;
      margin-right: 15px;
    }
    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #273c75;
      color: white;
      height: 100%;
      position: fixed;
      top: 0;
      left: -250px;
      transition: 0.3s;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      z-index: 1000;
    }
    .sidebar.active { left: 0; }
    .profile {
      text-align: center;
      padding: 25px 10px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .profile img {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      margin-bottom: 10px;
      object-fit: cover;
      border: 3px solid #fff;
    }
    .profile h3 { margin: 5px 0; font-size: 18px; }
    .profile p { margin: 0; font-size: 14px; color: #dcdde1; }
    .upload-btn {
      margin-top: 10px;
      background: #44bd32;
      border: none;
      padding: 8px 15px;
      color: white;
      border-radius: 20px;
      cursor: pointer;
      font-size: 14px;
      transition: 0.3s;
    }
    .upload-btn:hover { background: #2ecc71; }
    .sidebar a {
      display: block;
      padding: 14px 20px;
      color: white;
      text-decoration: none;
      border-left: 4px solid transparent;
      transition: 0.3s;
      font-size: 15px;
    }
    .sidebar a:hover {
      background: #40739e;
      border-left: 4px solid #44bd32;
    }
    .logout {
      background: #e84118;
      text-align: center;
      padding: 14px;
    }
    .logout a {
      color: white;
      text-decoration: none;
      font-weight: bold;
    }
    /* Main Content */
    .main {
      margin-left: 0;
      padding: 20px;
      transition: margin-left 0.3s;
      min-height: calc(100vh - 60px); /* full screen minus navbar */
      display: flex;
      flex-direction: column;
    }
    .sidebar.active ~ .main { margin-left: 250px; }

    .cards {
      flex: 1;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      align-items: stretch;
    }
    .card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      text-align: center;
      font-size: 17px;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: 0.3s;
    }
    .cards a.card {
       text-decoration: none;
       color: inherit;
       display: block;
    }
    .card span {
      display: block;
      margin-top: 8px;
      font-size: 24px;
      color: #1e3799;
    }
    .card:hover { transform: translateY(-5px); }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <span class="menu-btn" onclick="toggleSidebar()">&#9776;</span>
    <h2>Business Ledger Dashboard</h2>
  </div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div>
      <div class="profile">
        <img src="<?php echo $profile_pic; ?>" alt="Profile">
        <h3><?php echo $name; ?></h3>
        <p><?php echo $email; ?></p>

        <!-- Custom Upload Button -->
        <form action="upload_profile.php" method="POST" enctype="multipart/form-data" id="uploadForm">
          <input type="file" name="profile_pic" id="fileInput" accept="image/*" style="display:none;" onchange="document.getElementById('uploadForm').submit();">
          <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click();">
            Upload Image
          </button>
        </form>
      </div>

      <a href="dashboard.php">Dashboard</a>
      <a href="add_items.php">Add Items</a>
      <a href="order.php">Orders</a>
      <a href="reports.php">Reports</a>
      <a href="add_sale.php">Add Sales</a>
    </div>
    <div class="logout">
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main" id="main">
    <div class="cards">
      <a href="products.php" class="card">Products <span><?php echo $total_products; ?></span></a>
      <a href="sales.php" class="card">Sales <span><?php echo $total_sales; ?></span></a>
      <a href="expenses.php" class="card">Expenses <span>$<?php echo number_format($total_expenses,2); ?></span></a>
      <a href="profit.php" class="card">Profit <span>$<?php echo number_format($total_profit,2); ?></span></a>
      <a href="discount.php" class="card">Discounts <span>$<?php echo number_format($total_discounts,2); ?></span></a>
      <a href="loss.php" class="card">Loss <span>$<?php echo number_format($total_loss,2); ?></span></a>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
      document.getElementById("main").classList.toggle("active");
    }
  </script>

</body>
</html>
