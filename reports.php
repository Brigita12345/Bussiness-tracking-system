<?php
session_start();
include("connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch monthly profit/loss data
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
    ORDER BY s.sales_date ASC
";

$result = $conn->query($sql);
$data = $result->fetch_all(MYSQLI_ASSOC);

// Prepare arrays for Chart.js
$months = [];
$profits = [];
$losses = [];

foreach($data as $row) {
    $months[] = $row['month'];
    $profit = $row['total_sales'] - $row['total_expenses'];
    $profits[] = $profit > 0 ? $profit : 0;   // show only positive profits
    $losses[] = $profit < 0 ? abs($profit) : 0; // show only actual losses
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profit & Loss Reports</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    canvas {
        margin-top: 30px;
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
  <h2>Profit & Loss Trend Reports</h2>

  <canvas id="profitChart" height="100"></canvas>
  <canvas id="lossChart" height="100"></canvas>

  <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>

<script>
const months = <?php echo json_encode($months); ?>;
const profits = <?php echo json_encode($profits); ?>;
const losses = <?php echo json_encode($losses); ?>;

// Profit Chart
const ctxProfit = document.getElementById('profitChart').getContext('2d');
new Chart(ctxProfit, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Monthly Profit ($)',
            data: profits,
            borderColor: '#44bd32',
            backgroundColor: 'rgba(68, 189, 50, 0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Profit ($)' } },
            x: { title: { display: true, text: 'Month' } }
        }
    }
});

// Loss Chart
const ctxLoss = document.getElementById('lossChart').getContext('2d');
new Chart(ctxLoss, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Monthly Loss ($)',
            data: losses,
            borderColor: '#e84118',
            backgroundColor: 'rgba(232, 65, 24, 0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Loss ($)' } },
            x: { title: { display: true, text: 'Month' } }
        }
    }
});
</script>
</body>
</html>
