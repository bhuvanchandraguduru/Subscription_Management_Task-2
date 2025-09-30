<?php
session_start();
include 'db.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile = $_POST['mobile'] ?? '';

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ?");
    $stmt->execute([$name, $email, $mobile, $user_id]);

    header("Location: profile.php?updated=1");
    exit;
}

// ✅ Handle email reminder toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_reminders'])) {
    $enabled = isset($_POST['reminders']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET email_reminders = ? WHERE id = ?");
    $stmt->execute([$enabled, $user_id]);

    echo json_encode(["status" => "success", "enabled" => $enabled]);
    exit;
}

// ✅ Fetch user info
$stmt = $pdo->prepare("SELECT name, email, mobile, email_reminders FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ✅ Fetch total subscriptions
$stmt2 = $pdo->prepare("SELECT COUNT(*) as total FROM subscriptions WHERE user_id = ?");
$stmt2->execute([$user_id]);
$total_subscriptions = $stmt2->fetch()['total'];

// Check if edit mode is active
$editMode = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Subscription Management</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }
    .switch input {display:none;}
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0;
      right: 0; bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 24px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    input:checked + .slider {
      background-color: #4caf50;
    }
    input:checked + .slider:before {
      transform: translateX(26px);
    }
  </style>
</head>
<body class="light">
  <header class="navbar">
    <div class="logo">Subscription Manager</div>
    <nav>
      <a href="user_dashboard.php">Dashboard</a>
      <a href="profile.php">Profile</a>
      <button id="themeToggle">🌓</button>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="hero">
    <div class="hero-content">
      <h1>My Profile</h1>
      <p>View and manage your personal information.</p>

      <?php if (isset($_GET['updated'])): ?>
        <p style="color: green;">✅ Profile updated successfully!</p>
      <?php endif; ?>

      <div class="profile-card">
        <?php if ($editMode): ?>
          <!-- ✅ Edit Form -->
          <form method="POST">
            <input type="hidden" name="update_profile" value="1">

            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

            <label>Mobile:</label>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" placeholder="Enter mobile number">

            <button type="submit">Save Changes</button>
            <a href="profile.php">Cancel</a>
          </form>
        <?php else: ?>
          <!-- ✅ Read-only Profile -->
          <h2><?php echo htmlspecialchars($user['name'] ?? ''); ?></h2>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
          <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile'] ?? 'Not set'); ?></p>
          <p><strong>Total Subscriptions:</strong> <?php echo $total_subscriptions; ?></p>

          <a href="profile.php?edit=1" class="btn">Edit Profile</a>
        <?php endif; ?>

        <!-- ✅ Email Reminders -->
        <section class="settings-section">
          <h2>Email Reminders</h2>
          <label class="switch">
            <input type="checkbox" id="emailReminderToggle" <?= $user['email_reminders'] ? 'checked' : '' ?>>
            <span class="slider"></span>
          </label>
          <span id="reminderStatus"><?= $user['email_reminders'] ? 'Enabled' : 'Disabled'; ?></span>
        </section>

        <!-- Logout -->
        <section class="settings-section">
          <button id="logoutBtn" class="btn-logout" onclick="location.href='logout.php'">Log Out</button>
        </section>
      </div>
    </div>
  </main>

  <footer>
    &copy; 2025 Subscription Management
  </footer>

  <script>
    // ✅ Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    themeToggle.addEventListener('click', () => {
      body.classList.toggle('dark');
      body.classList.toggle('light');
      localStorage.setItem('theme', body.className);
    });
    const savedTheme = localStorage.getItem('theme');
    if(savedTheme) body.className = savedTheme;

    // ✅ Email reminder toggle
    const reminderToggle = document.getElementById('emailReminderToggle');
    const reminderStatus = document.getElementById('reminderStatus');

    reminderToggle.addEventListener('change', () => {
      const formData = new FormData();
      formData.append('toggle_reminders', '1');
      if (reminderToggle.checked) {
        formData.append('reminders', '1');
      }

      fetch("profile.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        reminderStatus.textContent = data.enabled == 1 ? "Enabled" : "Disabled";
      });
    });
  </script>
</body>
</html>
