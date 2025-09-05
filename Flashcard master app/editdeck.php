<?php
// Bắt đầu một phiên làm việc (session) để quản lý trạng thái đăng nhập của người dùng.
session_start();

// Kiểm tra xem biến session 'UserID' đã được thiết lập chưa.
// Điều này xác định xem người dùng đã đăng nhập hay chưa.
if (!isset($_SESSION['UserID'])) {
    // Nếu người dùng chưa đăng nhập, chuyển hướng họ đến trang đăng nhập.
    header('Location: login.php');
    // Dừng việc thực thi script để ngăn không cho phần HTML được hiển thị.
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Deck</title>
    <link
      href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
      rel="stylesheet" />
    <link rel="icon" type="image/png" href="flashcard.png">
  </head>
  <link rel="stylesheet" href="darkmode.css" />
  <body class="bg-gray-100 min-h-screen font-sans">
    <header class="shadow-md py-4 px-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Edit Deck</h1>
      <div class="flex space-x-4">
        <a
          href="/api/settings.php"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
          Thông tin cá nhân
        </a>
        <a
          href="index.php"
          class="text-gray-200 hover:text-blue-200 transition">
          ← Quay lại Decks
        </a>
      </div>
    </header>
    <link rel="stylesheet" href="editdeck.css" />

    <main class="p-6">
      <h2 id="deckTitle" class="text-xl font-semibold mb-4"></h2>

      <div class="mb-6">
        <button
          id="addCardBtn"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          + Add Card 
        </button>
        <button id="learnBtn" class="action-btn btn-new bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Studying
        </button>
      </div>

      <div id="cardList" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      </div>
    </main>

    <div
      id="cardModal"
      class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg w-96 shadow-lg">
        <h3 class="text-lg font-semibold mb-4">Add New Card</h3>
        <img id="imagePreview" src="" class="hidden w-full h-48 object-cover rounded-lg mb-2" />
        <input type="hidden" id="cardIdInput" />
        <input
          type="text"
          id="frontInput"
          placeholder="Front"
          class="w-full border p-2 mb-2" />
        <input
          type="text"
          id="backInput"
          placeholder="Back"
          class="w-full border p-2 mb-4" />
        <input
          type="file"
          id="imageInput"
          accept="image/*"
          class="w-full mb-2" />
        <div class="flex justify-end space-x-2">
          <button id="cancelCardBtn" class="px-4 py-2 border rounded">
            Cancel
          </button>
          <button
            id="saveCardBtn"
            class="px-4 py-2 bg-green-600 text-white rounded">
            Save
          </button>
        </div>
      </div>
    </div>

    <script type="module" src="editdeck.js"></script>
  </body>
</html>