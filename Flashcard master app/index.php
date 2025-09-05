<?php
// Bắt đầu một phiên làm việc (session)
// Session dùng để lưu trữ thông tin người dùng trong suốt quá trình họ truy cập website
session_start();

// Kiểm tra xem biến session 'UserID' đã được thiết lập (tức là người dùng đã đăng nhập) hay chưa
if (!isset($_SESSION['UserID'])) {
    // Nếu chưa đăng nhập, chuyển hướng người dùng đến trang đăng nhập
    header("Location: /api/login.php");
    // Dừng việc thực thi script để không có nội dung HTML nào được hiển thị
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Flashcard Dashboard</title>
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

  <main class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Your Decks</h2>
      <button id="createDeckBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ New Deck</button>
    </div>

    <div id="deckList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      </div>
  </main>

  <div id="deckModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96 shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Create New Deck</h3>
      <input type="text" id="newDeckName" placeholder="Deck Name" class="w-full border p-2 mb-4" />
      <div class="flex justify-end space-x-2">
        <button id="cancelDeckBtn" class="px-4 py-2 border rounded">Cancel</button>
        <button id="saveDeckBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </div>
    </div>
  </div>

  <div id="renameModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300">
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg transform scale-95 transition-transform duration-300">
          <h2 class="text-2xl font-bold mb-4">Đổi tên Deck</h2>
          <input type="text" id="renameDeckName" placeholder="Nhập tên mới cho deck" class="w-full p-2 mb-4 border border-gray-300 rounded-md">
          <div class="flex justify-end space-x-2">
              <button id="cancelRenameBtn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition-colors">Hủy</button>
              <button id="saveRenameBtn" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-colors">Lưu</button>
          </div>
      </div>
  </div>

  <div id="deleteModal" class="modal-overlay hidden">
      <div class="modal-content">
          <h3 class="text-2xl font-bold mb-4">Xác Nhận Xóa</h3>
          <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa bộ thẻ này không?</p>
          <div class="flex justify-center space-x-4">
              <button id="cancelDeleteBtn" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg font-medium hover:bg-gray-400 transition">Hủy</button>
              <button id="confirmDeleteBtn" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">Xóa</button>
          </div>
      </div>
  </div>

  <script type="module" src="./main.js"></script>
</body>
</html>