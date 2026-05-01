<?php

require_once 'validate.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineBase - Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <a href="index.php"><img src="images/logo.jpg" alt="CineBase Logo"></a>
  <nav>
    <a href="index.php">Home</a>
    <a href="forms.php">Forms</a>
    <a href="reports.php">Reports</a>
  </nav>
</header>

<div class="banner">
  <h2>Welcome to CineBase</h2>
  <p>Your movie database for browsing, reviewing, and managing films.</p>
</div>

<main>
  <div class="home-links">
    <a href="forms.php">Forms</a>
    <a href="reports.php">Reports</a>
  </div>

  <p style="text-align:center; color:#555;">
    Use <strong>Forms</strong> to search, add, and update movie records.<br>
    Use <strong>Reports</strong> to view formatted data from the database.
  </p>
</main>

<footer>
  <p>&copy; <?php echo date('Y'); ?> CineBase Movie Database - ITFN 2214 Group Project</p>
</footer>

</body>
</html>
