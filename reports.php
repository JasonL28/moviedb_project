<?php
// Report 1 created by Jason
require_once 'connect.php';

// Report 1: Top 10 Highest-Rated Movies with Average User Rating
$report1_data = [];
$report1_error = "";

try {
    $sql = "SELECT m.title, m.release_year, m.director, m.imdb_rating,
            COALESCE(AVG(r.rating), 0) as avg_user_rating,
            COUNT(DISTINCT r.review_id) as review_count
            FROM movies m
            INNER JOIN reviews r ON m.movie_id = r.movie_id
            WHERE r.rating >= 1.0
            GROUP BY m.movie_id
            HAVING COUNT(DISTINCT r.review_id) >= 1
            ORDER BY m.imdb_rating DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $report1_data = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $report1_error = "Error loading report: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineBase - Reports</title>
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

<main>
  <a href="index.php" class="back-link">&larr; Back to Home</a>

  <h1>Database Reports</h1>

  <!-- Report 1: Jason -->
  <div class="report-card">
    <h3>Report 1: Top 10 Highest-Rated Movies</h3>
    <p class="member">Jason</p>
    
    <?php if (!empty($report1_error)): ?>
      <p class="error"><?php echo $report1_error; ?></p>
    <?php elseif (count($report1_data) > 0): ?>
      <table border="1" cellpadding="8" style="width:100%; border-collapse: collapse; margin-top: 10px;">
        <tr style="background-color:#f2f2f2;">
          <th>Title</th>
          <th>Year</th>
          <th>Director</th>
          <th>IMDb Rating</th>
          <th>Avg User Rating</th>
          <th># of Reviews</th>
        </tr>
        <?php foreach ($report1_data as $movie): ?>
        <tr>
          <td><?php echo htmlspecialchars($movie['title'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($movie['release_year'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($movie['director'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($movie['imdb_rating'] ?? ''); ?></td>
          <td><?php echo number_format($movie['avg_user_rating'], 1); ?></td>
          <td><?php echo $movie['review_count']; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p class="desc" style="margin-top: 10px;">* Only movies with at least 1 user review are included.</p>
    <?php else: ?>
      <p>No data available. Add some reviews to see the report.</p>
    <?php endif; ?>
  </div>

  <!-- Report 2: Isaac (to be completed by groupmate) -->
  <div class="report-card">
    <h3>Report 2: Movies by Genre</h3>
    <p class="member">Isaac</p>
  </div>

  <!-- Report 3: Josue (to be completed by groupmate) -->
  <div class="report-card">
    <h3>Report 3: Recently Added to Watchlists</h3>
    <p class="member">Josue</p>
  </div>

</main>

<footer>
  <p>&copy; <?php echo date('Y'); ?> CineBase Movie Database - ITFN 2214 Group Project</p>
</footer>

</body>
</html>