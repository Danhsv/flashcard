<?php
// Bắt đầu một phiên làm việc (session)
session_start();

// Kiểm tra xem biến session UserID đã được thiết lập chưa
if (!isset($_SESSION['UserID'])) {
    // Nếu chưa, chuyển hướng người dùng đến trang đăng nhập
    header("Location: /api/login.php");
    // Dừng việc thực thi script
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="delete.css">
  <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-black dark:text-white min-h-screen font-sans">
  <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-700">Flashcard App</h1>
    <nav class="space-x-4">
      <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 ">Dashboard</a>
      <a href="index.php" class="text-gray-600 hover:text-blue-600">Decks</a>
      <a href="/api/settings.php" class="text-gray-600 hover:text-blue-600">Settings</a>
    </nav>
  </header>

  <main class="container mx-auto p-4">
    <div class="relative max-w-2xl mx-auto my-8">
      <input type="text" id="search-input" placeholder="Tìm kiếm từ khóa trong các thẻ của bạn..." 
             class="w-full p-4 pl-12 pr-4 text-lg rounded-full shadow-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow">
      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
    </div>

    <div id="search-results-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 my-8 hidden">
    </div>
  </main>
  
  <script src="./dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</body>
</html>