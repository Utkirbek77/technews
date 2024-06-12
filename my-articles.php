<?php

session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research_contact_app";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  error_log("Connection failed: " . $conn->connect_error);
  die("Connection failed: " . $conn->connect_error);
}

function uploadToSupabase($file)
{
  $url = 'https://oyxqznxevfuplxlbraej.supabase.co/storage/v1/object/media/' . uniqid() . '_' . basename($file['name']);
  $headers = [
    'apikey: 1ccfac427a825be49bb64cc2b3326f22',
    'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im95eHF6bnhldmZ1cGx4bGJyYWVqIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTcxODIwOTExOCwiZXhwIjoyMDMzNzg1MTE4fQ.yL4n6prCd9JiweqEgAnRR8Mh_GI7neJO2prcVsHKmB0',
    'Content-Type: ' . $file['type']
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file['tmp_name']));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch) || $result === false) {
    error_log('Curl error: ' . curl_error($ch));
    die('Error: ' . curl_error($ch));
  }
  if ($result === false) {
    error_log('Curl error: ' . curl_error($ch));
    die('Error: ' . curl_error($ch));
  }

  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($httpCode >= 400) {
    error_log('HTTP error: ' . $httpCode . ' Response: ' . $result);
    die('HTTP error: ' . $httpCode . ' Response: ' . $result);
  }

  curl_close($ch);

  $response = json_decode($result, true);
  if (isset($response['error'])) {
    error_log('Supabase error: ' . $response['error']['message']);
    die('Supabase error: ' . $response['error']['message']);
  }

  return str_replace('/object/media', '/object/public/media', $url);
}

// Check connection
if ($conn->connect_error) {
  error_log("Connection failed: " . $conn->connect_error);
  die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    author INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Handle delete article request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
  $articleId = isset($_POST['articleId']) ? (int) $_POST['articleId'] : null;
  $author = $_SESSION['id']; // Assuming the user ID is stored in the session
  if ($articleId) {
    $sql = "DELETE FROM articles WHERE id=$articleId";
    if ($conn->query($sql) === TRUE) {
      header("Location: my-articles.php");
      exit;
    } else {
      error_log("Error: " . $conn->error);
      echo "Error: " . $conn->error;
    }
  }
}

// Save new or updated article
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['content']) && !isset($_POST['delete'])) {
  $author = $_SESSION['id']; // Ensure author is set
  $title = $conn->real_escape_string($_POST['title']);
  $content = $conn->real_escape_string($_POST['content']);
  $articleId = isset($_POST['articleId']) ? (int) $_POST['articleId'] : null;
  $imagePath = null;

  // Handle image upload to Supabase
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $imagePath = uploadToSupabase($_FILES['image']);
  }

  // Insert or update article in the database
  if ($articleId) {
    if ($imagePath !== null) {
      $sql = "UPDATE articles SET title='$title', content='$content', image='$imagePath', author='$author' WHERE id=$articleId";
    } else {
      $sql = "UPDATE articles SET title='$title', content='$content', author='$author' WHERE id=$articleId";
    }
  } else {
    if ($imagePath !== null) {
      $sql = "INSERT INTO articles (title, content, image, author) VALUES ('$title', '$content', '$imagePath', '$author')";
    } else {
      $sql = "INSERT INTO articles (title, content, author) VALUES ('$title', '$content', '$author')";
    }
  }

  if ($conn->query($sql) === TRUE) {
    header("Location: my-articles.php");
    exit;
  } else {
    error_log("Error: " . $sql . "<br>" . $conn->error);
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
}

// Fetch articles
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT id, title, content, image FROM articles WHERE author='{$_SESSION['id']}' AND title LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>My Articles</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@1.15.0/dist/full.css" rel="stylesheet">
  <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
  <script>
    CKEDITOR.on('instanceReady', function(ev) {
      ev.editor.on('change', function() {
        ev.editor.updateElement();
      });
    });
  </script>
  <style>
    .line-clamp-4 {
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .modal-content {
      width: 80%;
      max-width: 1000px;
      max-height: 90vh;
      /* Ensure the modal does not exceed the viewport height */
      overflow-y: auto;
      /* Enable vertical scrolling */
    }

    .sticky-buttons {
      position: sticky;
      bottom: 0;
      background: white;
      padding-top: 10px;
      padding-bottom: 10px;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
      <div class="flex">
        <input type="text" id="search" placeholder="Search..." class="input input-bordered w-full max-w-xs" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button onclick="searchArticles()" class="btn btn-primary ml-2">Search</button>
        <?php if (isset($_GET['search']) && $_GET['search'] != '') : ?>
          <button onclick="clearSearch()" class="btn btn-secondary ml-2">Clear</button>
        <?php endif; ?>
      </div>
      <button onclick="showModal()" class="btn btn-primary">Create Article</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<div class='card bg-base-100 shadow-xl cursor-pointer' onclick='editArticle(" . json_encode($row) . ")'>";
          echo "<figure><img src='" . (!empty($row['image']) ? htmlspecialchars($row['image']) : "https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.jpg") . "' alt='Article Image' class='object-cover w-full h-48'/></figure>";
          echo "<div class='card-body'>";
          echo "<h2 class='card-title'>" . htmlspecialchars($row['title']) . "</h2>";
          echo "<div class='line-clamp-4 overflow-hidden'>" . html_entity_decode($row['content']) . "</div>";
          echo "<div class='flex justify-between items-center mt-4'>";
          echo "</div>";
          echo "</div>";
        }
      } else {
        echo "No articles found.";
      }
      ?>
    </div>
  </div>

  <!-- Modal -->
  <div id="articleModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <form action="my-articles.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg modal-content">
      <div class="p-6">
        <h2 class="text-2xl mb-4" id="modalTitle">Create New Article</h2>
        <input type="hidden" id="articleId" name="articleId">
        <div class="overflow-auto">
          <div class="mb-4">
            <label for="title" class="block text-gray-700">Title</label>
            <input type="text" id="title" name="title" class="input input-bordered w-full" required>
          </div>
          <div class="mb-4">
            <label for="content" class="block text-gray-700">Content</label>
            <textarea id="content" name="content" class="textarea textarea-bordered w-full" required></textarea>
          </div>
          <div class="mb-4">
            <label for="image" class="block text-gray-700">Image</label>
            <input type="file" id="image" name="image" class="file-input file-input-bordered w-full max-w-xs" accept="image/*">
            <img id="currentImage" src="" alt="Current Image" class="mt-2 hidden" style="max-width: 100%; height: auto; max-height: 200px; object-fit: cover;">
          </div>
        </div>
      </div>
      <div class="flex justify-end sticky-buttons">
        <button type="button" onclick="hideModal()" class="btn btn-secondary mr-2">Cancel</button>
        <button type="submit" class="btn btn-primary mr-2">Save</button>
        <button type="submit" name="delete" value="delete" class="btn btn-error mr-2" id="deleteButton">Delete</button>
      </div>
  </div>
  </form>

  <script>
    function showModal() {
      document.getElementById('articleId').value = '';
      document.getElementById('title').value = '';
      document.getElementById('content').value = '';
      CKEDITOR.replace('content');
      document.getElementById('modalTitle').textContent = 'Create New Article';
      document.getElementById('deleteButton').classList.add('hidden');
      document.getElementById('currentImage').classList.add('hidden');
      document.getElementById('articleModal').classList.remove('hidden');
    }

    function hideModal() {
      document.getElementById('articleModal').classList.add('hidden');
      CKEDITOR.instances.content.destroy();
    }

    function editArticle(article) {
      document.getElementById('articleId').value = article.id;
      document.getElementById('title').value = article.title;
      document.getElementById('content').value = article.content;
      CKEDITOR.replace('content');
      document.getElementById('modalTitle').textContent = 'Edit Article';
      document.getElementById('deleteButton').classList.remove('hidden');
      if (article.image) {
        document.getElementById('currentImage').src = article.image;
        document.getElementById('currentImage').classList.remove('hidden');
      } else {
        document.getElementById('currentImage').classList.add('hidden');
      }
      document.getElementById('articleModal').classList.remove('hidden');
    }

    function searchArticles() {
      const search = document.getElementById('search').value;
      window.location.href = `my-articles.php?search=${search}`;
    }

    function clearSearch() {
      window.location.href = 'my-articles.php';
    }
  </script>
</body>

</html>