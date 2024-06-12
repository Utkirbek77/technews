<?php
session_start();
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password is empty
$dbname = "research_contact_app";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$errorMessage = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  header("Location: home.php");
  exit;
}
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $formUsername = $conn->real_escape_string($_POST['username']);
  $formPassword = $_POST['password'];

  if (isset($_POST['register'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $hashedPassword = password_hash($formPassword, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password) VALUES ('$formUsername', '$email', '$hashedPassword')";
    if ($conn->query($sql) === TRUE) {
      $_SESSION['loggedin'] = true;
      $_SESSION['username'] = $formUsername;
      $_SESSION['email'] = $email;
      $_SESSION['id'] = $conn->insert_id;
      header("Location: home.php");
      exit;
    } else {
      $errorMessage = 'Error registering: ' . $conn->error;
    }
  } elseif (isset($_POST['login'])) {
    $sql = "SELECT id, username, email, password FROM users WHERE username = '$formUsername'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if (password_verify($formPassword, $row['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['id'] = $row['id'];
        header("Location: home.php");
        exit;
      } else {
        $errorMessage = 'Invalid username or password.';
      }
    } else {
      $errorMessage = 'User does not exist.';
    }
  }
}

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: login.php");
  exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Research Contact App</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.2/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">

  <div class="card w-1/2 bg-base-100 shadow-xl">
    <div class="card-body">
      <h1 class="text-2xl font-bold mb-4 text-center">Welcome to Research Contact App</h1>
      <?php if ($action !== 'dashboard'): ?>
        <div role="tablist" class="tabs tabs-boxed">
          <a role="tab" class="tab <?php echo $action === 'login' ? 'tab-active' : ''; ?>" href="?action=login">Log In</a>
          <a role="tab" class="tab <?php echo $action === 'register' ? 'tab-active' : ''; ?>" href="?action=register">Sign
            Up</a>
        </div>
      <?php endif; ?>

      <?php if ($action === 'login' || $action === 'register'): ?>
        <form class="flex flex-col gap-4" action="?action=<?= htmlspecialchars($action) ?>" method="post">
          <h2 class="text-xl"><?= $action === 'login' ? 'Login' : 'Register' ?> Form</h2>
          <?php if (!empty($errorMessage)): ?>
            <p style="color: red;"><?= htmlspecialchars($errorMessage) ?></p>
          <?php endif; ?>

          <label class="input input-bordered flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
              <path
                d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM12.735 14c.618 0 1.093-.561.872-1.139a6.002 6.002 0 0 0-11.215 0c-.22.578.254 1.139.872 1.139h9.47Z" />
            </svg>
            <input name="username" required type="text" class="grow" placeholder="Username" />
          </label>

          <?php if ($action !== 'login'): ?>
            <label class="input input-bordered flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                <path
                  d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z" />
                <path
                  d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z" />
              </svg>
              <input type="text" class="grow" name="email" required placeholder="Email" />
            </label>
          <?php endif; ?>

          <label class="input input-bordered flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
              <path fill-rule="evenodd"
                d="M14 6a4 4 0 0 1-4.899 3.899l-1.955 1.955a.5.5 0 0 1-.353.146H5v1.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2.293a.5.5 0 0 1 .146-.353l3.955-3.955A4 4 0 1 1 14 6Zm-4-2a.75.75 0 0 0 0 1.5.5.5 0 0 1 .5.5.75.75 0 0 0 1.5 0 2 2 0 0 0-2-2Z"
                clip-rule="evenodd" />
            </svg>
            <input name="password" required type="password" class="grow" placeholder="Password" />
          </label>

          <input type="submit" class="mt-4 btn btn-active btn-primary"
            value="<?= $action === 'login' ? 'Login' : 'Register' ?>" name="<?= $action ?>">
        </form>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>
