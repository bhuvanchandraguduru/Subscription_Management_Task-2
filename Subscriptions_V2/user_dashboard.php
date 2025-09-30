<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Subscription Manager</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .converter-box {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        max-width: 400px;
        margin: 20px auto;
      }
      .converter-box h3 {
        margin-top: 0;
      }
      .converter-box input, .converter-box select, .converter-box button {
        margin: 5px 0;
        padding: 8px;
        width: 100%;
      }
      .converter-result {
        margin-top: 10px;
        font-weight: bold;
      }
    </style>
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
<div class="logo">🔔 Subscription Management System</div>
    <nav>
        <a href="user_dashboard.php">My Subscriptions</a>
        <a href="#" id="addSubBtn">Add Subscription</a>
        <a href="settings.php">Settings</a>
        <a href="profile.php">profile</a>
        <button id="themeToggle">🌓</button>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </nav>
</header>

<main class="hero">
    <div class="overlay"></div>
    <div class="hero-content">
    <h1>Welcome 👋</h1>
    <h2>Your Subscriptions</h2>
    <form id="searchForm">
    <input type="search" id="search-input" name="q" placeholder="Search...">
    <button type="submit">Search</button>
</form>
    <form action="subscriptions_import.php" method="POST" enctype="multipart/form-data">
      <input type="file" name="csv_file" accept=".csv" required>
      <button type="submit">Import Subscriptions</button>
    </form>
    <!-- Search Results Modal -->
<div id="searchModal" class="popup" style="display:none;">
    <h3>Search Results</h3>
    <div id="searchResults"></div>
    <button class="close-btn" onclick="$('#searchModal').fadeOut()">Close</button>
</div>

     <table class="table" id="subscriptionsTable">
        <thead>
            <tr>
                <th>Service</th>
                <th>Amount</th>
                <th>Billing Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Charts Section -->
    <h2>Analytics</h2>
    <div style="width:90%; max-width:800px; margin:auto;">
        <canvas id="statusChart"></canvas>
    </div>
    <div style="width:90%; max-width:800px; margin:auto; margin-top:30px;">
        <canvas id="expenseChart"></canvas>
    </div>
    <div class="converter-box">
      <h3>Currency Converter 💱</h3>
      <input type="number" id="amount" placeholder="Enter amount" required>
      <select id="fromCurrency">
        <option value="USD">USD</option>
        <option value="INR" selected>INR</option>
        <option value="EUR">EUR</option>
        <option value="GBP">GBP</option>
        <option value="JPY">JPY</option>
      </select>
      <select id="toCurrency">
        <option value="USD" selected>USD</option>
        <option value="INR">INR</option>
        <option value="EUR">EUR</option>
        <option value="GBP">GBP</option>
        <option value="JPY">JPY</option>
      </select>
      <button id="convertBtn">Convert</button>
      <div class="converter-result" id="conversionResult"></div>
    </div>
    </div>
</main>

<!-- Modal (unchanged) -->
<div id="subscriptionModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <h3 id="modalTitle">Add Subscription</h3>
    <form id="subscriptionForm" autocomplete="off">
        <input type="hidden" name="id" id="subId">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
        <label>Service</label>
        <input type="text" name="service" required>
        <label>Subscription Amount</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Billing Date</label>
        <input type="date" name="billing_date" required>
        <label>Status</label>
        <select name="status"><option>Active</option><option>Expired</option></select>
        <div style="margin-top:12px;">
            <button id="submitBtn" type="submit">Save</button>
            <button id="cancelBtn" type="button">Cancel</button>
        </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function(){
    let statusChart, expenseChart;

    loadSubscriptions();

    function loadSubscriptions(){
        $.get("subscriptions_api.php?action=list", function(res){
            if (!res.success) { alert(res.error || "Failed to load"); return; }
            let rows = "", active=0, expired=0;
            let monthly = {};

            res.data.forEach(sub => {
                rows += `<tr>
                    <td>${sub.service}</td>
                    <td>$${parseFloat(sub.amount).toFixed(2)}</td>
                    <td>${sub.billing_date}</td>
                    <td>${sub.status}</td>
                    <td>
                        <button onclick="editSub(${sub.id}, '${sub.service}', '${sub.amount}', '${sub.billing_date}', '${sub.status}')">Edit</button>
                        <button onclick="deleteSub(${sub.id})">Delete</button>
                    </td>
                </tr>`;
                if(sub.status === "Active") active++; else expired++;

                let month = sub.billing_date.substring(0,7);
                monthly[month] = (monthly[month] || 0) + parseFloat(sub.amount);
            });

            $("#subscriptionsTable tbody").html(rows || "<tr><td colspan='5'>No subscriptions.</td></tr>");

            drawCharts(active, expired, monthly);
        }, "json");
    }

    function drawCharts(active, expired, monthly){
        if(statusChart) statusChart.destroy();
        if(expenseChart) expenseChart.destroy();

        const ctx1 = document.getElementById('statusChart').getContext('2d');
        statusChart = new Chart(ctx1, {
            type: 'doughnut',
            data: { labels: ["Active","Expired"], datasets:[{ data:[active,expired], backgroundColor:["#4caf50","#f44336"] }] }
        });

        const ctx2 = document.getElementById('expenseChart').getContext('2d');
        expenseChart = new Chart(ctx2, {
            type: 'bar',
            data: { labels:Object.keys(monthly), datasets:[{ label:"Monthly Expenditure", data:Object.values(monthly), backgroundColor:"#2196f3" }] }
        });
    }

    // Modal controls (same as before)
    $("#addSubBtn").click(function(e){ e.preventDefault(); $("#subId").val(""); $("#subscriptionForm")[0].reset(); $("#subscriptionModal").fadeIn(); });
    $("#closeModal, #cancelBtn").click(()=> $("#subscriptionModal").fadeOut());
    $(window).click(e=>{ if(e.target.id==="subscriptionModal") $("#subscriptionModal").fadeOut(); });

    $("#subscriptionForm").submit(function(e){
        e.preventDefault();
        let id = $("#subId").val();
        let action = id ? "update" : "create";
        $.post("subscriptions_api.php?action="+action, $(this).serialize(), function(res){
            if(res.success){ $("#subscriptionModal").fadeOut(); loadSubscriptions(); }
            else alert(res.error || "Failed");
        }, "json");
    });

    window.editSub = function(id, s, a, d, st){
        $("#subId").val(id); $("input[name=service]").val(s); $("input[name=amount]").val(a);
        $("input[name=billing_date]").val(d); $("select[name=status]").val(st);
        $("#subscriptionModal").fadeIn();
    }
    window.deleteSub = id=>{
        if(confirm("Are you sure?")) $.post("subscriptions_api.php?action=delete",{id},res=>{
            if(res.success) loadSubscriptions(); else alert(res.error || "Delete failed");
        },"json");
    }
    // AJAX search
$("#searchForm").submit(function(e){
    e.preventDefault();
    let query = $("#search-input").val().trim();
    if(!query) return;

    $.getJSON("search_api.php", {q: query}, function(res){
        if(!res.success){ alert(res.error || "Search failed"); return; }
        let html = "";
        if(res.data.length === 0){
            html = "<p>No subscriptions found.</p>";
        } else {
            res.data.forEach(sub=>{
                html += `<div class="result-item">
                    <strong>${sub.service}</strong> — $${parseFloat(sub.amount).toFixed(2)} 
                    (Next billing: ${sub.billing_date})
                </div>`;
            });
        }
        $("#searchResults").html(html);
        $("#searchModal").fadeIn();
    });
});
$("#currencyForm").submit(function(e){
    e.preventDefault();
    let amount = $("#amount").val();
    let from = $("#from").val();
    let to = $("#to").val();

    $.getJSON("currency_api.php", { amount, from, to }, function(res){
        if(res.success){
            $("#conversionResult").text(
                `${amount} ${from} = ${res.converted.toFixed(2)} ${to} (Rate: ${res.rate.toFixed(4)})`
            );
        } else {
            $("#conversionResult").text("Conversion failed: " + (res.error || "Unknown error"));
        }
    });
});

});
</script>
<footer class="footer"><p>&copy; 2025 Subscription Manager</p></footer>
</body>
</html>
