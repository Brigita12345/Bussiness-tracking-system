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
$losses = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monthly Loss Report</title>
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
        color: #e84118;
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
        background: #fdf2f2;
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
    .card .loss {
        font-size: 20px;
        font-weight: 700;
    }
    /* Color coding for loss */
    .loss-positive { background: #ffcccc; color: #e84118; }  /* actual loss */
    .loss-none { background: #dff9fb; color: #22a6b3; }     /* no loss */

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
        background: #e84118;
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
        background: #e84118;
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-back:hover {
        background: #c23616;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Monthly Loss Dashboard</h2>

  <div class="cards">
    <?php if(count($losses) > 0): ?>
      <?php foreach($losses as $row): 
        $loss = $row['total_expenses'] - $row['total_sales'];
        if($loss > 0) {
            $class = 'loss-positive';
            $displayLoss = '$' . number_format($loss, 2);
        } else {
            $class = 'loss-none';
            $displayLoss = 'No loss this month';
        }
      ?>
        <div class="card <?php echo $class; ?>">
          <div class="month"><?php echo htmlspecialchars($row['month']); ?></div>
          <div class="loss"><?php echo $displayLoss; ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-data">No loss data available.</div>
    <?php endif; ?>
  </div>

  <table>
    <tr>
      <th>Month</th>
      <th>Sales ($)</th>
      <th>Expenses ($)</th>
      <th>Loss ($)</th>
    </tr>
    <?php if(count($losses) > 0): ?>
      <?php foreach($losses as $row): 
        $loss = $row['total_expenses'] - $row['total_sales'];
        $displayLoss = $loss > 0 ? '$' . number_format($loss, 2) : 'No loss this month';
      ?>
        <tr>
          <td><?php echo htmlspecialchars($row['month']); ?></td>
          <td><?php echo number_format($row['total_sales'],2); ?></td>
          <td><?php echo number_format($row['total_expenses'],2); ?></td>
          <td><?php echo $displayLoss; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="4" class="no-data">No loss data available.</td></tr>
    <?php endif; ?>
  </table>

  <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>
</body>
</html>
