<?php
session_start();
include 'db.php';

// Check login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Handle new reminder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscription_name'], $_POST['end_date'])) {
    $subscription_name = $_POST['subscription_name'];
    $end_date = $_POST['end_date'];
    $remind_before_days = (int)$_POST['remind_before_days'];

    $stmt = $pdo->prepare("INSERT INTO reminders (user_id, subscription_name, end_date, remind_before_days) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $subscription_name, $end_date, $remind_before_days]);

    header("Location: settings.php?saved=1");
    exit;
}

// Fetch reminders for display
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY end_date ASC");
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - Subscription Management</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="light">

<header class="navbar">
  <div class="logo">Subscription Manager</div>
  <nav>
    <a href="user_dashboard.php">Dashboard</a>
    <a href="settings.php">Settings</a>
    <button id="themeToggle">🌓</button>
  </nav>
</header>

<main class="hero">
  <div class="hero-content">
    <h1>Settings</h1>

    <?php if (isset($_GET['saved'])): ?>
      <p style="color:green;">✅ Reminder saved!</p>
    <?php endif; ?>

    <!-- Create Reminder -->
    <section class="settings-section">
      <h2>Create Reminder</h2>
      <form method="POST">
        <label>Subscription Name:</label>
        <input type="text" name="subscription_name" placeholder="e.g., Netflix" required>

        <label>End Date:</label>
        <input type="date" name="end_date" required>

        <label>Remind Before (days):</label>
        <input type="number" name="remind_before_days" min="1" max="30" value="3" required>

        <button type="submit">Save Reminder</button>
      </form>
    </section>

    <!-- Show Reminders -->
    <section class="settings-section">
      <h2>My Reminders</h2>
      <?php if ($reminders): ?>
        <table>
          <tr>
            <th>Subscription</th>
            <th>End Date</th>
            <th>Remind Before</th>
          </tr>
          <?php foreach ($reminders as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['subscription_name']); ?></td>
              <td><?php echo htmlspecialchars($r['end_date']); ?></td>
              <td><?php echo htmlspecialchars($r['remind_before_days']); ?> days</td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>No reminders yet.</p>
      <?php endif; ?>
    </section>

    <!-- Email Reminders Switch -->
    <section class="settings-section">
      <h2>Email Reminders</h2>
      <label class="switch">
        <input type="checkbox" id="emailReminderToggle" checked>
        <span class="slider"></span>
      </label>
      <span id="reminderStatus">Enabled</span>
    </section>

    <!-- Logout -->
    <section class="settings-section">
      <a href="logout.php"><button class="btn-logout">Log Out</button></a>
    </section>
  </div>
</main>

<footer>
  &copy; 2025 Subscription Management
</footer>

<script>
  // Theme toggle
  const themeToggle = document.getElementById('themeToggle');
  const body = document.body;
  themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark');
    body.classList.toggle('light');
    localStorage.setItem('theme', body.className);
  });
  const savedTheme = localStorage.getItem('theme');
  if(savedTheme) body.className = savedTheme;

  // Email reminder switch
  const emailToggle = document.getElementById('emailReminderToggle');
  const reminderStatus = document.getElementById('reminderStatus');
  emailToggle.addEventListener('change', () => {
    reminderStatus.textContent = emailToggle.checked ? "Enabled" : "Disabled";
  });
</script>

</body>
</html>
