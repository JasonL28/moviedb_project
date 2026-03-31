<?php
$query_error = "";
$query_success = "";
$q_title = "";
$q_director = "";
$q_year = "";
$q_genre = "";
$q_min_rating = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["q_title"]) || isset($_POST["q_genre"]) || isset($_POST["q_director"]) || isset($_POST["q_min_rating"]) || isset($_POST["q_year"])) {
    
    $q_title = isset($_POST["q_title"]) ? trim($_POST["q_title"]) : "";
    $q_genre = isset($_POST["q_genre"]) ? trim($_POST["q_genre"]) : "";
    $q_director = isset($_POST["q_director"]) ? trim($_POST["q_director"]) : "";
    $q_min_rating = isset($_POST["q_min_rating"]) ? trim($_POST["q_min_rating"]) : "";
    $q_year = isset($_POST["q_year"]) ? trim($_POST["q_year"]) : "";
    
    $valid = true;
    
    if (empty($q_title) && empty($q_genre) && empty($q_director) && empty($q_min_rating) && empty($q_year)) {
        $query_error = "Please enter at least one search criteria.";
        $valid = false;
    }
    
    if (!empty($q_year) && (!is_numeric($q_year) || $q_year < 1900 || $q_year > 2030)) {
        $query_error = "Year must be between 1900 and 2030.";
        $valid = false;
    }
    
    if ($valid) {
        $query_success = "Search submitted successfully!";
    }
}

$update_error = "";
$update_success = "";
$update_user_id = "";
$update_movie_id = "";
$update_watched = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"]) && isset($_POST["movie_id"])) {
    
    $update_user_id = isset($_POST["user_id"]) ? trim($_POST["user_id"]) : "";
    $update_movie_id = isset($_POST["movie_id"]) ? trim($_POST["movie_id"]) : "";
    $update_watched = isset($_POST["watched"]) ? trim($_POST["watched"]) : "";
    
    $valid = true;
    $error_messages = [];
    
    if (empty($update_user_id)) {
        $error_messages[] = "User ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_user_id) || $update_user_id < 1) {
        $error_messages[] = "User ID must be a positive number.";
        $valid = false;
    }
    
    if (empty($update_movie_id)) {
        $error_messages[] = "Movie ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_movie_id) || $update_movie_id < 1) {
        $error_messages[] = "Movie ID must be a positive number.";
        $valid = false;
    }
    
    if (!$valid) {
        $update_error = implode("<br>", $error_messages);
    }
    
    if ($valid) {
        $update_success = "Watchlist updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineBase - Forms</title>
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

  <h1>Database Forms</h1>

  <!-- ===== QUERY FORM ===== -->
  <div class="form-section">
    <h2>Query Form - Search Movies</h2>
    <p class="desc">Search the database by title, genre, director, or rating.</p>

    <form action="forms.php" method="POST">
      <?php if (!empty($query_error)) echo "<p class='error'>$query_error</p>"; ?>
      <?php if (!empty($query_success)) echo "<p class='success'>$query_success</p>"; ?>

      <label for="q_title">Movie Title</label>
      <input type="text" id="q_title" name="q_title" placeholder="e.g. The Godfather" value="<?php echo htmlspecialchars($q_title); ?>">

      <label for="q_genre">Genre</label>
      <select id="q_genre" name="q_genre">
        <option value="">-- Any Genre --</option>
        <option value="Action" <?php if ($q_genre == "Action") echo "selected"; ?>>Action</option>
        <option value="Comedy" <?php if ($q_genre == "Comedy") echo "selected"; ?>>Comedy</option>
        <option value="Drama" <?php if ($q_genre == "Drama") echo "selected"; ?>>Drama</option>
        <option value="Horror" <?php if ($q_genre == "Horror") echo "selected"; ?>>Horror</option>
        <option value="Romance" <?php if ($q_genre == "Romance") echo "selected"; ?>>Romance</option>
        <option value="Sci-Fi" <?php if ($q_genre == "Sci-Fi") echo "selected"; ?>>Sci-Fi</option>
        <option value="Thriller" <?php if ($q_genre == "Thriller") echo "selected"; ?>>Thriller</option>
        <option value="Animation" <?php if ($q_genre == "Animation") echo "selected"; ?>>Animation</option>
      </select>

      <label for="q_director">Director</label>
      <input type="text" id="q_director" name="q_director" placeholder="e.g. Christopher Nolan" value="<?php echo htmlspecialchars($q_director); ?>">

      <label for="q_min_rating">Minimum IMDb Rating</label>
      <select id="q_min_rating" name="q_min_rating">
        <option value="">-- Any Rating --</option>
        <option value="9" <?php if ($q_min_rating == "9") echo "selected"; ?>>9+</option>
        <option value="8" <?php if ($q_min_rating == "8") echo "selected"; ?>>8+</option>
        <option value="7" <?php if ($q_min_rating == "7") echo "selected"; ?>>7+</option>
        <option value="6" <?php if ($q_min_rating == "6") echo "selected"; ?>>6+</option>
        <option value="5" <?php if ($q_min_rating == "5") echo "selected"; ?>>5+</option>
      </select>

      <label for="q_year">Release Year</label>
      <input type="number" id="q_year" name="q_year" placeholder="e.g. 2010" min="1900" max="2030" value="<?php echo htmlspecialchars($q_year); ?>">

      <button type="submit">Search</button>
    </form>
  </div>

  <!-- ===== UPDATE FORM ===== -->
  <div class="form-section">
    <h2>Update Form - Mark Movie Watched</h2>
    <p class="desc">Update watchlist status.</p>

    <form action="forms.php" method="POST">
      <?php if (!empty($update_error)) echo "<p class='error'>$update_error</p>"; ?>
      <?php if (!empty($update_success)) echo "<p class='success'>$update_success</p>"; ?>
      <label for="w_user_id">User ID</label>
      <input type="number" id="w_user_id" name="user_id" placeholder="e.g. 1" min="1" value="<?php echo htmlspecialchars($update_user_id); ?>">

      <label for="w_movie_id">Movie ID</label>
      <input type="number" id="w_movie_id" name="movie_id" placeholder="e.g. 10" min="1" value="<?php echo htmlspecialchars($update_movie_id); ?>">

      <label for="w_status">Watched</label>
      <select id="w_status" name="watched">
        <option value="1" <?php if ($update_watched == "1") echo "selected"; ?>>Yes</option>
        <option value="0" <?php if ($update_watched == "0") echo "selected"; ?>>No</option>
      </select>

      <button type="submit">Update</button>
    </form>
  </div>

  <!-- ===== INSERT FORM ===== -->
  <div class="form-section">
    <h2>Insert Form - Add New Records</h2>
    <p class="desc">Register a user</p>

    <h3>Register a New User</h3>
    <form action="forms.php" method="POST">
      <label for="u_username">Username</label>
      <input type="text" id="u_username" name="u_username" placeholder="e.g. moviefan99">

      <label for="u_email">Email</label>
      <input type="email" id="u_email" name="u_email" placeholder="e.g. user@example.com">

      <label for="u_fname">First Name</label>
      <input type="text" id="u_fname" name="u_fname" placeholder="e.g. Jane">

      <label for="u_lname">Last Name</label>
      <input type="text" id="u_lname" name="u_lname" placeholder="e.g. Doe">

      <label for="u_sub">Subscription</label>
      <select id="u_sub" name="u_sub">
        <option value="Free">Free</option>
        <option value="Basic">Basic</option>
        <option value="Premium">Premium</option>
      </select>
      
      <label>Join Date:</label>
        <input type="date" name="join_date">

      <button type="submit">Register User</button>
    </form>
  </div>

</main>

<footer>
  <p>&copy; <?php echo date('Y'); ?> CineBase Movie Database - ITFN 2214 Group Project</p>
</footer>

</body>
</html>
