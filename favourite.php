<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "vet_help";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php"); // Redirect to login page if not logged in
    exit();
}

// Check if clinic_id is provided
if (isset($_GET['clinic_id'])) {
    $clinic_id = $_GET['clinic_id'];

    // Check if the clinic is not already in favorites
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT * FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows == 0) {
        // Insert the favorite clinic into the database
        $insert_sql = "INSERT INTO user_favorites (user_id, clinic_id) VALUES ($user_id, $clinic_id)";
        $conn->query($insert_sql);
    } else {
        // Remove the favorite clinic from the database
        $delete_sql = "DELETE FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
        $conn->query($delete_sql);
    }
}

// Fetch favorite clinics for the current user
$user_id = $_SESSION['user_id'];
$sql = "SELECT vc.clinic_id, vc.clinic_name, vc.address, vc.phone_number, vc.rating_percentage, vc.reviews_count 
        FROM veterinary_clinic vc
        INNER JOIN user_favorites uf ON vc.clinic_id = uf.clinic_id
        WHERE uf.user_id = $user_id";
$result = $conn->query($sql);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (your head section remains the same) ... -->
</head>
<body>
    <?php include('header.html'); ?>
    <div class="container">
        <h1 class="mt-5 mb-4">Favorite Clinics</h1>

        <!-- Favorite clinic cards -->
        <div id="favoriteContainer" class="row">
            <?php
            // Display favorite clinic cards
            while ($row = $result->fetch_assoc()) {
            ?>
                <div class="col-md-4 clinic-card">
                    <div class="card mb-4 shadow-sm">
                        <!-- Link to clinic details page with clinic_id parameter -->
                        <a href="clinic_details.php?clinic_id=<?php echo $row['clinic_id']; ?>">
                            <h2 class="text-center mt-3"><?php echo $row['clinic_name']; ?></h2>
                        </a>
                        <p class="text-center">Rating: <?php echo $row['rating_percentage']; ?>%</p>
                        <p class="text-center">Address: <?php echo $row['address']; ?></p>
                        <p class="text-center">Phone: <?php echo $row['phone_number']; ?></p>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
<!-- Bootstrap and jQuery scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php include('footer.html'); ?>
</body>
</html>
