<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require_once "db.php"; // include your DB connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Subscription Manager</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const savedTheme = localStorage.getItem("theme") || "light";
    document.body.classList.add(savedTheme);
    const toggleBtn = document.getElementById("themeToggle");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", function () {
        document.body.classList.toggle("dark");
        document.body.classList.toggle("light");
        const newTheme = document.body.classList.contains("dark") ? "dark" : "light";
        localStorage.setItem("theme", newTheme);
      });
    }
  });
</script>

<body>
<header class="navbar">
  <div class="logo">👑 Subscription Management System - Admin</div>
  <nav>
      <a href="admin_dashboard.php">All Subscriptions</a>
      <a href="profile.php">Profile</a>
      <button id="themeToggle">🌓</button>
      <a href="logout.php" class="btn btn-danger">Logout</a>
  </nav>
</header>

<main class="hero">
  <div class="overlay"></div>
  <div class="hero-content">
    <h1>Welcome, Admin 👋</h1>
    <h2>All Users & Subscriptions</h2>

    <!-- Search -->
    <form id="searchForm">
    <input type="search" id="search-input" name="q" placeholder="Search...">
    <button type="submit">Search</button>
</form>
<div id="searchModal" class="popup" style="display:none;">
    <h3>Search Results</h3>
    <div id="searchResults"></div>
    <button class="close-btn" onclick="$('#searchModal').fadeOut()">Close</button>
</div>
    <!-- Import -->
    <form action="subscriptions_import.php" method="POST" enctype="multipart/form-data">
      <input type="file" name="csv_file" accept=".csv" required>
      <button type="submit">Import Subscriptions</button>
    </form>

    <!-- Table -->
    <table class="table" id="subscriptionsTable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Service</th>
          <th>Amount</th>
          <th>Billing Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $q = $_GET['q'] ?? '';
        $sql = "SELECT s.id, s.service, s.amount, s.billing_date, s.status, u.name, u.email
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id";
        if ($q) {
          $sql .= " WHERE u.name LIKE ? OR u.email LIKE ? OR s.service LIKE ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        } else {
          $stmt = $pdo->query($sql);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
          foreach ($rows as $row) {
            echo "<tr>
              <td>{$row['name']}</td>
              <td>{$row['email']}</td>
              <td>{$row['service']}</td>
              <td>$" . number_format($row['amount'], 2) . "</td>
              <td>{$row['billing_date']}</td>
              <td>{$row['status']}</td>
              <td>
                <a href='edit_subscription.php?id={$row['id']}'>Edit</a> | 
                <a href='delete_subscription.php?id={$row['id']}' onclick=\"return confirm('Delete this subscription?')\">Delete</a>
              </td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='7'>No subscriptions found.</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <!-- Charts -->
    <h2>Analytics</h2>
    <div style="width:90%; max-width:800px; margin:auto;">
        <canvas id="statusChart"></canvas>
    </div>
    <div style="width:90%; max-width:800px; margin:auto; margin-top:30px;">
        <canvas id="expenseChart"></canvas>
    </div>

  </div>
</main>

<footer class="footer"><p>&copy; 2025 Subscription Manager</p></footer>

<script>
  // You can feed analytics data via PHP -> JS (json_encode)
  const subsData = <?= json_encode($rows) ?>;

  let active=0, expired=0, monthly={};
  subsData.forEach(sub=>{
    if(sub.status === "Active") active++; else expired++;
    let month = sub.billing_date.substring(0,7);
    monthly[month] = (monthly[month]||0)+parseFloat(sub.amount);
  });

  new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels:["Active","Expired"], datasets:[{ data:[active,expired], backgroundColor:["#4caf50","#f44336"] }] }
  });

  new Chart(document.getElementById('expenseChart').getContext('2d'), {
    type:'bar',
    data:{ labels:Object.keys(monthly), datasets:[{ label:"Monthly Expenditure", data:Object.values(monthly), backgroundColor:"black"}] }
  });
</script>
</body>
</html>
