<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  if (basename($_SERVER['PHP_SELF']) !== 'home.php' && basename($_SERVER['PHP_SELF']) !== 'article.php') {
    header("Location: login.php");
    exit;
  }
}
?>
<div class="navbar bg-base-100">
  <div class="flex-1">
    <a href="home.php" class="btn btn-ghost text-xl">Tech News</a>
  </div>
  <div class="flex-none">
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
      <div class="dropdown dropdown-end">
        <?php if (basename($_SERVER['PHP_SELF']) !== 'my-articles.php') : ?>
          <a href="my-articles.php" class="btn btn-primary btn-outline mr-4">Manage Articles</a>
        <?php endif; ?>
      </div>
      <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
            </svg>
          </div>
        </div>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
          <li>
            <a class="justify-between" href="edit-profiles.php">
              Profile
            </a>
          </li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </div>
    <?php else : ?>
      <a href="login.php" class="btn btn-primary ml-4">Login</a>
    <?php endif; ?>
  </div>
</div>