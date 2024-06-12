<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// Database connection setup
$servername = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "research_contact_app";
$conn = new mysqli($servername, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch current user information from database
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['email'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $email = $conn->real_escape_string($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Update profile logic
  if ($password === $confirm_password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updateSql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssi", $username, $email, $hashedPassword, $_SESSION['id']);
    if ($updateStmt->execute()) {
      $message = 'Profile updated successfully.';
    } else {
      $message = 'Error updating profile: ' . $conn->error;
    }
    $updateStmt->close();
  } else {
    $message = 'Passwords do not match.';
  }

  // Delete account logic
  if (isset($_POST['delete_account'])) {
    $deleteSql = "DELETE FROM users WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $_SESSION['id']);
    if ($deleteStmt->execute()) {
      session_destroy();
      header("Location: login.php");
      exit;
    } else {
      $message = 'Error deleting account: ' . $conn->error;
    }
    $deleteStmt->close();
  }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@1.15.0/dist/full.css" rel="stylesheet">
</head>

<body>
  <?php include 'navbar.php'; ?>
  <div class="flex items-center justify-center min-h-screen px-4 sm:px-0">

    <div class="card w-full sm:w-1/2 bg-base-100 shadow-xl">
      <div class="card-body">
        <h1 class="text-2xl font-bold mb-4 text-center">Edit Your Profile</h1>
        <?php if ($message) : ?>

          <div role="alert" class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span><?= htmlspecialchars($message) ?></span>
          </div>
        <?php endif; ?>
        <form action="edit-profiles.php" method="post" class="space-y-4 w-full">
          <div class="form-control">
            <label class="label">
              <span class="label-text">Username</span>
            </label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="input input-bordered w-full" />
          </div>
          <div class="form-control">
            <label class="label">
              <span class="label-text">Email</span>
            </label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="input input-bordered w-full" />
          </div>
          <div class="form-control">
            <label class="label">
              <span class="label-text">Password</span>
            </label>
            <input type="password" name="password" required class="input input-bordered w-full" />
          </div>
          <div class="form-control">
            <label class="label">
              <span class="label-text">Confirm Password</span>
            </label>
            <input type="password" name="confirm_password" required class="input input-bordered w-full" />
          </div>
          <div class="form-control mt-4">
            <input type="submit" value="Update Profile" class="btn btn-primary w-full sm:w-auto" />
          </div>
          <div class="form-control mt-4">
            <input type="submit" name="delete_account" value="Delete Account" class="btn btn-error w-full sm:w-auto" />
          </div>

        </form>
      </div>
    </div>

  </div>
</body>

</html>
