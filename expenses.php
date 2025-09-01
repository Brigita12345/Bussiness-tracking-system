<?php
session_start();
include("connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all expenses grouped by month
$sql = "
    SELECT 
        DATE_FORMAT(order_date, '%M %Y') AS month,
        SUM(buying_price * quantity + transport_fee) AS total_expenses
    FROM orders
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY order_date DESC
";
$result = $conn->query($sql);
$expenses = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monthly Expenses Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
        font-family: 'Roboto', sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 1000px;
        margin: 50px auto;
        background: #fff;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #273c75;
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
    .card .amount {
        font-size: 20px;
        font-weight: 700;
    }
    /* Color coding for expenses */
    .low { background: #dff9fb; color: #22a6b3; }
    .medium { background: #f6e58d; color: #f9ca24; }
    .high { background: #ffcccc; color: #e84118; }

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
        background: #273c75;
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
        background: #273c75;
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-back:hover {
        background: #192a56;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Monthly Expenses Dashboard</h2>

  <div class="cards">
    <?php if(count($expenses) > 0): ?>
      <?php foreach($expenses as $row): 
        $amount = $row['total_expenses'];
        if($amount < 1000) { $class = 'low'; }
        elseif($amount < 5000) { $class = 'medium'; }
        else { $class = 'high'; }
      ?>
        <div class="card <?php echo $class; ?>">
          <div class="month"><?php echo htmlspecialchars($row['month']); ?></div>
          <div class="amount">$<?php echo number_format($amount, 2); ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-data">No expenses recorded yet.</div>
    <?php endif; ?>
  </div>

  <table>
    <tr>
      <th>Month</th>
      <th>Total Expenses ($)</th>
    </tr>
    <?php if(count($expenses) > 0): ?>
      <?php foreach($expenses as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['month']); ?></td>
          <td><?php echo number_format($row['total_expenses'], 2); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="2" class="no-data">No expenses recorded yet.</td>
      </tr>
    <?php endif; ?>
  </table>

  <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>
</body>
</html>
