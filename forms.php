<?php
$query_error = "";
$query_success = "";
$q_title = "";
$q_director = "";
$q_year = "";
$q_genre = "";
$q_min_rating = "";
$q_title_error = "";
$q_genre_error = "";
$q_director_error = "";
$q_rating_error = "";
$q_year_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["q_title"]) || isset($_POST["q_genre"]) || isset($_POST["q_director"]) || isset($_POST["q_min_rating"]) || isset($_POST["q_year"])) {
    
    $q_title = isset($_POST["q_title"]) ? trim($_POST["q_title"]) : "";
    $q_genre = isset($_POST["q_genre"]) ? trim($_POST["q_genre"]) : "";
    $q_director = isset($_POST["q_director"]) ? trim($_POST["q_director"]) : "";
    $q_min_rating = isset($_POST["q_min_rating"]) ? trim($_POST["q_min_rating"]) : "";
    $q_year = isset($_POST["q_year"]) ? trim($_POST["q_year"]) : "";
    
    $valid = true;
    $error_messages = [];
    
    // Validate Title
    if (empty($q_title)) {
        $q_title_error = "Movie Title is required.";
        $error_messages[] = "Movie Title is required.";
        $valid = false;
    } elseif (strlen($q_title) > 255) {
        $q_title_error = "Movie Title cannot exceed 255 characters.";
        $error_messages[] = "Movie Title cannot exceed 255 characters.";
        $valid = false;
    }
    
    // Validate Genre
    if (empty($q_genre)) {
        $q_genre_error = "Please select a genre.";
        $error_messages[] = "Please select a genre.";
        $valid = false;
    } else {
        $valid_genres = ["Action", "Comedy", "Drama", "Horror", "Romance", "Sci-Fi", "Thriller", "Animation"];
        if (!in_array($q_genre, $valid_genres)) {
            $q_genre_error = "Please select a valid genre from the list.";
            $error_messages[] = "Please select a valid genre from the list.";
            $valid = false;
        }
    }
    
    // Validate Director
    if (empty($q_director)) {
        $q_director_error = "Director Name is required.";
        $error_messages[] = "Director Name is required.";
        $valid = false;
    } elseif (strlen($q_director) > 100) {
        $q_director_error = "Director Name cannot exceed 100 characters.";
        $error_messages[] = "Director Name cannot exceed 100 characters.";
        $valid = false;
    }
    
    // Validate Rating
    if (empty($q_min_rating)) {
        $q_rating_error = "Minimum IMDb Rating is required.";
        $error_messages[] = "Minimum IMDb Rating is required.";
        $valid = false;
    } elseif (!is_numeric($q_min_rating)) {
        $q_rating_error = "Rating must be a number.";
        $error_messages[] = "Rating must be a number.";
        $valid = false;
    } elseif ($q_min_rating < 1 || $q_min_rating > 10) {
        $q_rating_error = "Rating must be between 1 and 10.";
        $error_messages[] = "Rating must be between 1 and 10.";
        $valid = false;
    }
    
    // Validate Year
    if (empty($q_year)) {
        $q_year_error = "Release Year is required.";
        $error_messages[] = "Release Year is required.";
        $valid = false;
    } elseif (!is_numeric($q_year)) {
        $q_year_error = "Year must be a number.";
        $error_messages[] = "Year must be a number.";
        $valid = false;
    } elseif ($q_year < 1888 || $q_year > 2030) {
        $q_year_error = "Year must be between 1888 and 2030.";
        $error_messages[] = "Year must be between 1888 and 2030.";
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
$update_user_id_error = "";
$update_movie_id_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"]) && isset($_POST["movie_id"])) {
    
    $update_user_id = isset($_POST["user_id"]) ? trim($_POST["user_id"]) : "";
    $update_movie_id = isset($_POST["movie_id"]) ? trim($_POST["movie_id"]) : "";
    $update_watched = isset($_POST["watched"]) ? trim($_POST["watched"]) : "";
    
    $valid = true;
    
    // Validate User ID
    if (empty($update_user_id)) {
        $update_user_id_error = "User ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_user_id) || $update_user_id < 1) {
        $update_user_id_error = "User ID must be a positive number.";
        $valid = false;
    }
    
    // Validate Movie ID
    if (empty($update_movie_id)) {
        $update_movie_id_error = "Movie ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_movie_id) || $update_movie_id < 1) {
        $update_movie_id_error = "Movie ID must be a positive number.";
        $valid = false;
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
      <?php if (!empty($query_success)) echo "<p class='success'>$query_success</p>"; ?>

      <label for="q_title">Movie Title</label>
      <input type="text" id="q_title" name="q_title" placeholder="e.g. The Godfather" value="<?php echo htmlspecialchars($q_title); ?>">
      <?php if (!empty($q_title_error)) echo "<p class='field-error'>$q_title_error</p>"; ?>

      <label for="q_genre">Genre</label>
      <select id="q_genre" name="q_genre">
        <option value="">-- Select a Genre --</option>
        <option value="Action" <?php if ($q_genre == "Action") echo "selected"; ?>>Action</option>
        <option value="Comedy" <?php if ($q_genre == "Comedy") echo "selected"; ?>>Comedy</option>
        <option value="Drama" <?php if ($q_genre == "Drama") echo "selected"; ?>>Drama</option>
        <option value="Horror" <?php if ($q_genre == "Horror") echo "selected"; ?>>Horror</option>
        <option value="Romance" <?php if ($q_genre == "Romance") echo "selected"; ?>>Romance</option>
        <option value="Sci-Fi" <?php if ($q_genre == "Sci-Fi") echo "selected"; ?>>Sci-Fi</option>
        <option value="Thriller" <?php if ($q_genre == "Thriller") echo "selected"; ?>>Thriller</option>
        <option value="Animation" <?php if ($q_genre == "Animation") echo "selected"; ?>>Animation</option>
      </select>
      <?php if (!empty($q_genre_error)) echo "<p class='field-error'>$q_genre_error</p>"; ?>

      <label for="q_director">Director</label>
      <input type="text" id="q_director" name="q_director" placeholder="e.g. Christopher Nolan" value="<?php echo htmlspecialchars($q_director); ?>">
      <?php if (!empty($q_director_error)) echo "<p class='field-error'>$q_director_error</p>"; ?>

      <label for="q_min_rating">Minimum IMDb Rating</label>
      <select id="q_min_rating" name="q_min_rating">
        <option value="">-- Select a Rating --</option>
        <option value="9" <?php if ($q_min_rating == "9") echo "selected"; ?>>9+</option>
        <option value="8" <?php if ($q_min_rating == "8") echo "selected"; ?>>8+</option>
        <option value="7" <?php if ($q_min_rating == "7") echo "selected"; ?>>7+</option>
        <option value="6" <?php if ($q_min_rating == "6") echo "selected"; ?>>6+</option>
        <option value="5" <?php if ($q_min_rating == "5") echo "selected"; ?>>5+</option>
      </select>
      <?php if (!empty($q_rating_error)) echo "<p class='field-error'>$q_rating_error</p>"; ?>

      <label for="q_year">Release Year</label>
      <input type="number" id="q_year" name="q_year" placeholder="e.g. 2010" min="1888" max="2030" value="<?php echo htmlspecialchars($q_year); ?>">
      <?php if (!empty($q_year_error)) echo "<p class='field-error'>$q_year_error</p>"; ?>

      <button type="submit">Search</button>
    </form>
  </div>

  <!-- ===== UPDATE FORM ===== -->
  <div class="form-section">
    <h2>Update Form - Mark Movie Watched</h2>
    <p class="desc">Update watchlist status.</p>

    <form action="forms.php" method="POST">
      <?php if (!empty($update_success)) echo "<p class='success'>$update_success</p>"; ?>

      <label for="w_user_id">User ID</label>
      <input type="number" id="w_user_id" name="user_id" placeholder="e.g. 1" min="1" value="<?php echo htmlspecialchars($update_user_id); ?>">
      <?php if (!empty($update_user_id_error)) echo "<p class='field-error'>$update_user_id_error</p>"; ?>

      <label for="w_movie_id">Movie ID</label>
      <input type="number" id="w_movie_id" name="movie_id" placeholder="e.g. 10" min="1" value="<?php echo htmlspecialchars($update_movie_id); ?>">
      <?php if (!empty($update_movie_id_error)) echo "<p class='field-error'>$update_movie_id_error</p>"; ?>

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