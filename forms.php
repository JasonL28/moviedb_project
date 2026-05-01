<?php

// Include database connection and reusable validation functions
require_once 'connect.php';
require_once 'validate.php';

// Initialize query form variables
$query_error = "";
$query_success = "";
$q_title = "";
$q_director = "";
$q_year = "";
$q_genre = "";
$q_min_rating = "";
$results = [];

// Initialize per-field error messages for query form
$q_title_error = "";
$q_genre_error = "";
$q_director_error = "";
$q_rating_error = "";
$q_year_error = "";

// Process query form when submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["q_title"]) || isset($_POST["q_genre"]) || isset($_POST["q_director"]) || isset($_POST["q_min_rating"]) || isset($_POST["q_year"])) {
    
    // Sanitize all inputs to prevent security issues
    $q_title = isset($_POST["q_title"]) ? sanitize($_POST["q_title"]) : "";
    $q_genre = isset($_POST["q_genre"]) ? sanitize($_POST["q_genre"]) : "";
    $q_director = isset($_POST["q_director"]) ? sanitize($_POST["q_director"]) : "";
    $q_min_rating = isset($_POST["q_min_rating"]) ? sanitize($_POST["q_min_rating"]) : "";
    $q_year = isset($_POST["q_year"]) ? sanitize($_POST["q_year"]) : "";
    
    $valid = true;
    $error_messages = [];
    
    // Validate Title - required only
    if (empty($q_title)) {
        $q_title_error = "Movie Title is required.";
        $error_messages[] = "Movie Title is required.";
        $valid = false;
    } elseif (strlen($q_title) > 255) {
        $q_title_error = "Movie Title cannot exceed 255 characters.";
        $error_messages[] = "Movie Title cannot exceed 255 characters.";
        $valid = false;
    }
    
    // Validate Genre - optional (only validate if provided)
    if (!empty($q_genre)) {
        $valid_genres = ["Action", "Adventure", "Animation", "Comedy", "Crime", "Drama", "Family", "Fantasy", "History", "Horror", "Mystery", "Romance", "Sci-Fi", "Thriller", "War", "Western"];
        if (!in_array($q_genre, $valid_genres)) {
            $q_genre_error = "Please select a valid genre from the list.";
            $error_messages[] = "Please select a valid genre from the list.";
            $valid = false;
        }
    }
    
    // Validate Director - optional (only validate if provided)
    if (!empty($q_director) && strlen($q_director) > 100) {
        $q_director_error = "Director Name cannot exceed 100 characters.";
        $error_messages[] = "Director Name cannot exceed 100 characters.";
        $valid = false;
    }
    
    // Validate Rating - optional (only validate if provided)
    if (!empty($q_min_rating)) {
        if (!is_numeric($q_min_rating)) {
            $q_rating_error = "Rating must be a number.";
            $error_messages[] = "Rating must be a number.";
            $valid = false;
        } elseif ($q_min_rating < 1 || $q_min_rating > 10) {
            $q_rating_error = "Rating must be between 1 and 10.";
            $error_messages[] = "Rating must be between 1 and 10.";
            $valid = false;
        }
    }
    
    // Validate Year - optional (only validate if provided)
    if (!empty($q_year)) {
        if (!is_numeric($q_year)) {
            $q_year_error = "Year must be a number.";
            $error_messages[] = "Year must be a number.";
            $valid = false;
        } elseif ($q_year < 1900 || $q_year > 2030) {
            $q_year_error = "Year must be between 1888 and 2030.";
            $error_messages[] = "Year must be between 1888 and 2030.";
            $valid = false;
        }
    }
    
    // If all validations pass, search the database
    if ($valid) {
        try {
            $sql = "SELECT DISTINCT m.title, m.release_year, m.director, m.imdb_rating,
                    GROUP_CONCAT(DISTINCT g.genre_name ORDER BY g.genre_name SEPARATOR ', ') as genres
                    FROM movies m
                    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
                    LEFT JOIN genres g ON mg.genre_id = g.genre_id
                    WHERE (m.title LIKE :title 
                    OR m.director LIKE :director 
                    OR m.release_year = :year
                    OR m.imdb_rating >= :rating)
                    AND m.movie_id IN (SELECT DISTINCT mg2.movie_id FROM movie_genres mg2)
                    GROUP BY m.movie_id
                    ORDER BY m.imdb_rating DESC";
            
            $stmt = $pdo->prepare($sql);
            
           $stmt->execute([
               ':title' => "%$q_title%",
               ':director' => "%$q_director%",
               ':year' => $q_year,
               ':rating' => $q_min_rating
           ]);
            
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                $query_success = "Found " . count($results) . " movie(s).";
            } else {
                $query_success = "No movies found matching your criteria.";
            }
            
        } catch(PDOException $e) {
            $query_error = "Database error: " . $e->getMessage();
        }
    }
} 

// UPDATE FORM 

// Initialize update form variables
$update_error = "";
$update_success = "";
$update_user_id = "";
$update_movie_id = "";
$update_watched = "";
$update_user_id_error = "";
$update_movie_id_error = "";

// Process update form when submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"]) && isset($_POST["movie_id"])) {
    
    $update_user_id = sanitize($_POST["user_id"]);
    $update_movie_id = sanitize($_POST["movie_id"]);
    $update_watched = isset($_POST["watched"]) ? sanitize($_POST["watched"]) : "";
    
    $valid = true;
    
    if (empty($update_user_id)) {
        $update_user_id_error = "User ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_user_id) || $update_user_id < 1) {
        $update_user_id_error = "User ID must be a positive number.";
        $valid = false;
    }
    
    if (empty($update_movie_id)) {
        $update_movie_id_error = "Movie ID is required.";
        $valid = false;
    } elseif (!is_numeric($update_movie_id) || $update_movie_id < 1) {
        $update_movie_id_error = "Movie ID must be a positive number.";
        $valid = false;
    }
    
    if ($valid) {
        try {
            $checkSql = "SELECT COUNT(*) FROM watchlist WHERE user_id = :user_id AND movie_id = :movie_id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([
                ':user_id' => $update_user_id,
                ':movie_id' => $update_movie_id
            ]);
            $exists = $checkStmt->fetchColumn();
            
            if ($exists > 0) {
                $sql = "UPDATE watchlist SET watched = :watched, date_added = CURDATE() 
                        WHERE user_id = :user_id AND movie_id = :movie_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':watched' => $update_watched,
                    ':user_id' => $update_user_id,
                    ':movie_id' => $update_movie_id
                ]);
                $update_success = "Watchlist updated successfully!";
            } else {
                $sql = "INSERT INTO watchlist (user_id, movie_id, date_added, watched) 
                        VALUES (:user_id, :movie_id, CURDATE(), :watched)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_id' => $update_user_id,
                    ':movie_id' => $update_movie_id,
                    ':watched' => $update_watched
                ]);
                $update_success = "Movie added to watchlist successfully!";
            }
        } catch(PDOException $e) {
            $update_error = "Database error: " . $e->getMessage();
        }
    }
}

// INSERT FORM Variables and Processing
$insert_success = "";
$ins_username = "";
$ins_email = "";
$ins_fname = "";
$ins_lname = "";
$ins_sub = "";
$ins_join_date = "";
$ins_username_error = "";
$ins_email_error = "";
$ins_fname_error = "";
$ins_lname_error = "";
$ins_sub_error = "";
$ins_join_date_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"]) && $_POST["form_type"] == "insert_user") {
    
    $ins_username  = sanitize($_POST["u_username"] ?? "");
    $ins_email     = sanitize($_POST["u_email"] ?? "");
    $ins_fname     = sanitize($_POST["u_fname"] ?? "");
    $ins_lname     = sanitize($_POST["u_lname"] ?? "");
    $ins_sub       = sanitize($_POST["u_sub"] ?? "");
    $ins_join_date = sanitize($_POST["join_date"] ?? "");
    
    $ins_username_error  = is_valid_username($ins_username);
    $ins_email_error     = is_valid_email($ins_email);
    $ins_fname_error     = is_valid_name($ins_fname, 'First name');
    $ins_lname_error     = is_valid_name($ins_lname, 'Last name');
    $ins_sub_error       = is_valid_subscription($ins_sub);
    $ins_join_date_error = is_valid_date($ins_join_date);
    
    $valid = empty($ins_username_error) && empty($ins_email_error) && 
             empty($ins_fname_error) && empty($ins_lname_error) && 
             empty($ins_sub_error) && empty($ins_join_date_error);
    
    if ($valid) {
        $insert_success = "User \"" . $ins_username . "\" registered successfully!";
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
       <option value="Adventure" <?php if ($q_genre == "Adventure") echo "selected"; ?>>Adventure</option>
       <option value="Animation" <?php if ($q_genre == "Animation") echo "selected"; ?>>Animation</option>
       <option value="Comedy" <?php if ($q_genre == "Comedy") echo "selected"; ?>>Comedy</option>
       <option value="Crime" <?php if ($q_genre == "Crime") echo "selected"; ?>>Crime</option>
       <option value="Drama" <?php if ($q_genre == "Drama") echo "selected"; ?>>Drama</option>
       <option value="Family" <?php if ($q_genre == "Family") echo "selected"; ?>>Family</option>
       <option value="Fantasy" <?php if ($q_genre == "Fantasy") echo "selected"; ?>>Fantasy</option>
       <option value="History" <?php if ($q_genre == "History") echo "selected"; ?>>History</option>
       <option value="Horror" <?php if ($q_genre == "Horror") echo "selected"; ?>>Horror</option>
       <option value="Mystery" <?php if ($q_genre == "Mystery") echo "selected"; ?>>Mystery</option>
       <option value="Romance" <?php if ($q_genre == "Romance") echo "selected"; ?>>Romance</option>
       <option value="Sci-Fi" <?php if ($q_genre == "Sci-Fi") echo "selected"; ?>>Sci-Fi</option>
       <option value="Thriller" <?php if ($q_genre == "Thriller") echo "selected"; ?>>Thriller</option>
       <option value="Western" <?php if ($q_genre == "Western") echo "selected"; ?>>Western</option>
      </select>

      <?php if (!empty($q_genre_error)) echo "<p class='field-error'>$q_genre_error</p>"; ?>

      <label for="q_director">Director</label>
      <input type="text" id="q_director" name="q_director" placeholder="e.g. Christopher Nolan" value="<?php echo htmlspecialchars($q_director); ?>">
      <?php if (!empty($q_director_error)) echo "<p class='field-error'>$q_director_error</p>"; ?>

      <label for="q_min_rating">Minimum IMDb Rating</label>
      <select id="q_min_rating" name="q_min_rating">
        <option value="">-- Select a Rating --</option>
        <option value="10" <?php if ($q_min_rating == "10") echo "selected"; ?>>10+</option>
        <option value="9" <?php if ($q_min_rating == "9") echo "selected"; ?>>9+</option>
        <option value="8" <?php if ($q_min_rating == "8") echo "selected"; ?>>8+</option>
        <option value="7" <?php if ($q_min_rating == "7") echo "selected"; ?>>7+</option>
        <option value="6" <?php if ($q_min_rating == "6") echo "selected"; ?>>6+</option>
        <option value="5" <?php if ($q_min_rating == "5") echo "selected"; ?>>5+</option>
        <option value="4" <?php if ($q_min_rating == "4") echo "selected"; ?>>4+</option>
        <option value="3" <?php if ($q_min_rating == "3") echo "selected"; ?>>3+</option>
        <option value="2" <?php if ($q_min_rating == "2") echo "selected"; ?>>2+</option>
        <option value="1" <?php if ($q_min_rating == "1") echo "selected"; ?>>1+</option>
      </select>
      <?php if (!empty($q_rating_error)) echo "<p class='field-error'>$q_rating_error</p>"; ?>

      <label for="q_year">Release Year</label>
      <input type="number" id="q_year" name="q_year" placeholder="e.g. 2010" min="1888" max="2030" value="<?php echo htmlspecialchars($q_year); ?>">
      <?php if (!empty($q_year_error)) echo "<p class='field-error'>$q_year_error</p>"; ?>

      <button type="submit">Search</button>
    </form>
  </div>

  <?php if (!empty($results)): ?>
  <div class="form-section">
    <h3>Search Results</h3>
    <table border="1" cellpadding="8" style="width:100%; border-collapse: collapse; background-color: white;">
      <tr>
        <th>Title</th>
        <th>Year</th>
        <th>Director</th>
        <th>IMDb Rating</th>
        <th>Genres</th>
      </tr>
      <?php foreach ($results as $movie): ?>
      <tr>
        <td><?php echo htmlspecialchars($movie['title'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($movie['release_year'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($movie['director'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($movie['imdb_rating'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($movie['genres'] ?? ''); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>

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
      <input type="hidden" name="form_type" value="insert_user">

      <?php if (!empty($insert_success)) echo "<p class='success'>$insert_success</p>"; ?>

      <label for="u_username">Username</label>
      <input type="text" id="u_username" name="u_username" placeholder="e.g. moviefan99" value="<?php echo htmlspecialchars($ins_username); ?>">
      <?php if (!empty($ins_username_error)) echo "<p class='field-error'>$ins_username_error</p>"; ?>

      <label for="u_email">Email</label>
      <input type="email" id="u_email" name="u_email" placeholder="e.g. user@example.com" value="<?php echo htmlspecialchars($ins_email); ?>">
      <?php if (!empty($ins_email_error)) echo "<p class='field-error'>$ins_email_error</p>"; ?>

      <label for="u_fname">First Name</label>
      <input type="text" id="u_fname" name="u_fname" placeholder="e.g. Jane" value="<?php echo htmlspecialchars($ins_fname); ?>">
      <?php if (!empty($ins_fname_error)) echo "<p class='field-error'>$ins_fname_error</p>"; ?>

      <label for="u_lname">Last Name</label>
      <input type="text" id="u_lname" name="u_lname" placeholder="e.g. Doe" value="<?php echo htmlspecialchars($ins_lname); ?>">
      <?php if (!empty($ins_lname_error)) echo "<p class='field-error'>$ins_lname_error</p>"; ?>

      <label for="u_sub">Subscription</label>
      <select id="u_sub" name="u_sub">
        <option value="Free" <?php if ($ins_sub == "Free") echo "selected"; ?>>Free</option>
        <option value="Basic" <?php if ($ins_sub == "Basic") echo "selected"; ?>>Basic</option>
        <option value="Premium" <?php if ($ins_sub == "Premium") echo "selected"; ?>>Premium</option>
      </select>
      <?php if (!empty($ins_sub_error)) echo "<p class='field-error'>$ins_sub_error</p>"; ?>

      <label for="join_date">Join Date</label>
      <input type="date" id="join_date" name="join_date" value="<?php echo htmlspecialchars($ins_join_date); ?>">
      <?php if (!empty($ins_join_date_error)) echo "<p class='field-error'>$ins_join_date_error</p>"; ?>

      <button type="submit">Register User</button>
    </form>
  </div>

</main>

<footer>
  <p>&copy; <?php echo date('Y'); ?> CineBase Movie Database - ITFN 2214 Group Project</p>
</footer>

</body>
</html>