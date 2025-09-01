<?php
session_start();
include("connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "
    SELECT 
        DATE_FORMAT(s.sales_date, '%M %Y') AS month,
        SUM(s.quantity * p.product_price) AS total_sales,
        (
            SELECT SUM(o.buying_price * o.quantity + o.transport_fee) 
            FROM orders o 
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = DATE_FORMAT(s.sales_date, '%Y-%m')
        ) AS total_expenses
    FROM sales s
    JOIN products p ON s.product_id = p.product_id
    GROUP BY DATE_FORMAT(s.sales_date, '%Y-%m')
    ORDER BY s.sales_date DESC
";
$result = $conn->query($sql);
$profits = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monthly Profit Report</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
        font-family: 'Roboto', sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 950px;
        margin: 50px auto;
        background: #fff;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #44bd32;
        margin-bottom: 30px;
        font-weight: 700;
    }
    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
        margin-bottom: 30px;
    }
    .card {
        flex: 1 1 200px;
        background: #f1f2f6;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .card .month {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 10px;
        color: #2f3640;
    }
    .card .profit {
        font-size: 20px;
        font-weight: 700;
    }
    /* Color coding for profit */
    .profit-positive { background: #dff9fb; color: #22a6b3; }
    .profit-low { background: #f6e58d; color: #f9ca24; }
    .profit-none { background: #ffcccc; color: #e84118; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 16px;
    }
    table th, table td {
        padding: 14px 12px;
        text-align: center;
        border-bottom: 1px solid #e1e1e1;
    }
    table th {
        background: #44bd32;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table tr:hover {
        background: #f1f2f6;
        transition: 0.3s;
    }
    .no-data {
        text-align: center;
        padding: 20px;
        color: #555;
        font-style: italic;
    }
    .btn-back {
        display: inline-block;
        margin-top: 25px;
        padding: 12px 25px;
        background: #44bd32;
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-back:hover {
        background: #2f9e1b;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Monthly Profit Dashboard</h2>

  <div class="cards">
    <?php if(count($profits) > 0): ?>
      <?php foreach($profits as $row): 
        $profit = $row['total_sales'] - $row['total_expenses'];
        if($profit < 0) { 
            $class = 'profit-none';
            $displayProfit = 'No profit made';
        } elseif($profit < 1000) {
            $class = 'profit-low';
            $displayProfit = '$' . number_format($profit, 2);
        } else {
            $class = 'profit-positive';
            $displayProfit = '$' . number_format($profit, 2);
        }
      ?>
        <div class="card <?php echo $class; ?>">
          <div class="month"><?php echo htmlspecialchars($row['month']); ?></div>
          <div class="profit"><?php echo $displayProfit; ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-data">No profit data available.</div>
    <?php endif; ?>
  </div>

  <table>
    <tr>
      <th>Month</th>
      <th>Sales ($)</th>
      <th>Expenses ($)</th>
      <th>Profit ($)</th>
    </tr>
    <?php if(count($profits) > 0): ?>
      <?php foreach($profits as $row): 
        $profit = $row['total_sales'] - $row['total_expenses'];
        $displayProfit = $profit < 0 ? 'No profit made' : '$' . number_format($profit, 2);
      ?>
        <tr>
          <td><?php echo htmlspecialchars($row['month']); ?></td>
          <td><?php echo number_format($row['total_sales'], 2); ?></td>
          <td><?php echo number_format($row['total_expenses'], 2); ?></td>
          <td><?php echo $displayProfit; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="4" class="no-data">No profit data available.</td></tr>
    <?php endif; ?>
  </table>

  <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>
</body>
</html>
