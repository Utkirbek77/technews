<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research_contact_app";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT articles.title, articles.content, articles.image, users.username AS author FROM articles JOIN users ON articles.author = users.id WHERE articles.id = $id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  $article = $result->fetch_assoc();
} else {
  die("Article not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($article['title']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@1.15.0/dist/full.css" rel="stylesheet">
</head>

<body>
  <?php include 'navbar.php'; ?>
  <div class="container mx-auto p-4">
    <div class="card bg-base-100 shadow-xl">
      <figure><img src="<?php echo !empty($article['image']) ? htmlspecialchars($article['image']) : 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.jpg'; ?>" alt="Article Image" class="object-cover w-full h-96" /></figure>
      <div class="card-body">
        <h2 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h2>
        <div><?php echo html_entity_decode($article['content']); ?></div>
        <p class="text-sm text-gray-500">By: <?php echo htmlspecialchars($article['author']); ?></p>
      </div>
    </div>
  </div>
</body>

</html>