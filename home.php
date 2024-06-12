
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

// Fetch articles
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT articles.id, articles.title, articles.content, articles.image, users.username AS author FROM articles JOIN users ON articles.author = users.id WHERE articles.title LIKE '%$search%'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@1.15.0/dist/full.css" rel="stylesheet">
  <style>
    .line-clamp-4 {
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>
  <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl">Articles</h1>
      <div class="flex">
        <input type="text" id="search" placeholder="Search..." class="input input-bordered w-full max-w-xs"
          value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button onclick="searchArticles()" class="btn btn-primary ml-2">Search</button>
        <?php if (isset($_GET['search']) && $_GET['search'] != ''): ?>
          <button onclick="clearSearch()" class="btn btn-secondary ml-2">Clear</button>
        <?php endif; ?>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<div class='card bg-base-100 shadow-xl'>";
          echo "<a href='article.php?id=" . $row['id'] . "'>";
          echo "<figure><img src='" . (!empty($row['image']) ? htmlspecialchars($row['image']) : "https://via.placeholder.com/300x200?text=No+Image+Available") . "' alt='Article Image' class='object-cover w-full h-48'/></figure>";
          echo "<div class='card-body'>";
          echo "<h2 class='card-title'>" . htmlspecialchars($row['title']) . "</h2>";
          echo "<p class='text-sm text-gray-500'>By: " . htmlspecialchars($row['author']) . "</p>";
          echo "<div class='line-clamp-4 overflow-hidden'>" . html_entity_decode($row['content']) . "</div>";
          echo "</div>";
          echo "</a>";
          echo "</div>";
        }
      } else {
        echo "No articles found.";
      }
      ?>
    </div>
  </div>
  <script>
    function searchArticles() {
      const search = document.getElementById('search').value;
      window.location.href = `home.php?search=${search}`;
    }

    function clearSearch() {
      window.location.href = 'home.php';
    }
  </script>
</body>
</html>
