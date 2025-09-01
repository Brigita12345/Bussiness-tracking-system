<?php
session_start();
include("connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch discount data
$sql = "
    SELECT 
        d.discount_date,
        p.product_name,
        p.product_price,
        d.discount_amount,
        (p.product_price - d.discount_amount) AS final_price,
        d.description
    FROM discounts d
    JOIN products p ON d.product_id = p.product_id
    ORDER BY d.discount_date ASC
";
$result = $conn->query($sql);
$discounts = $result->fetch_all(MYSQLI_ASSOC);

// Prepare monthly totals for chart
$monthly_totals = [];
foreach($discounts as $row) {
    $month = date("F Y", strtotime($row['discount_date']));
    if (!isset($monthly_totals[$month])) {
        $monthly_totals[$month] = 0;
    }
    $monthly_totals[$month] += $row['discount_amount'];
}

$months = array_keys($monthly_totals);
$totals = array_values($monthly_totals);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Discounts Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Roboto', sans-serif; background: #f5f6fa; margin:0; padding:0;}
    .container { max-width: 1000px; margin:50px auto; background:#fff; padding:30px 40px; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1);}
    h2 { text-align:center; color:#0097e6; margin-bottom:30px; font-weight:700;} /* Blue title */
    table { width:100%; border-collapse:collapse; font-size:16px; margin-top:30px;}
    table th, table td { padding:14px 12px; text-align:center; border-bottom:1px solid #e1e1e1;}
    table th { background:#27ae60; color:#fff; text-transform:uppercase; } /* Green table header */
    table tr:hover { background:#f1f2f6; transition:0.3s;}
    .no-data { text-align:center; padding:20px; color:#555; font-style:italic; }
    .btn-back { display:inline-block; margin-top:25px; padding:12px 25px; background:#0097e6; color:#fff; text-decoration:none; font-weight:500; border-radius:8px; transition:0.3s;}
    .btn-back:hover { background:#0652dd;}
    canvas { margin-top:30px; }
  </style>
</head>
<body>
<div class="container">
  <h2>Discount History & Trends</h2>

  <canvas id="discountChart" height="100"></canvas>

  <table>
    <tr>
      <th>Date</th>
      <th>Product</th>
      <th>Original Price ($)</th>
      <th>Discount ($)</th>
      <th>Final Price ($)</th>
      <th>Description</th>
    </tr>
    <?php if(count($discounts) > 0): ?>
      <?php foreach($discounts as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['discount_date']); ?></td>
          <td><?php echo htmlspecialchars($row['product_name']); ?></td>
          <td><?php echo number_format($row['product_price'],2); ?></td>
          <td><?php echo number_format($row['discount_amount'],2); ?></td>
          <td><?php echo number_format($row['final_price'],2); ?></td>
          <td><?php echo htmlspecialchars($row['description']); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="6" class="no-data">No discounts recorded yet.</td></tr>
    <?php endif; ?>
  </table>

  <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>

<script>
const months = <?php echo json_encode($months); ?>;
const totals = <?php echo json_encode($totals); ?>;

const ctx = document.getElementById('discountChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Total Discount ($)',
            data: totals,
            backgroundColor: 'rgba(39, 174, 96, 0.7)', // Green bars
            borderColor: 'rgba(0, 149, 230, 1)',       // Blue border
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Total Discount ($)' } },
            x: { title: { display: true, text: 'Month' } }
        }
    }
});
</script>
</body>
</html>
