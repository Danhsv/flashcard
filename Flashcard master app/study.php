<?php
// Bắt đầu một phiên làm việc (session).
session_start();

// Kiểm tra xem biến session 'UserID' đã được thiết lập chưa.
// Đây là bước kiểm tra bảo mật để đảm bảo người dùng đã đăng nhập.
if (!isset($_SESSION['UserID'])) {
    // Nếu chưa đăng nhập, chuyển hướng người dùng về trang đăng nhập.
    header('Location: login.php'); 
    // Dừng việc thực thi script.
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Study Flashcards</title>
  <link rel="stylesheet" href="study.css">
  <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body>
  <a
      href="/index.php"
      class="back-button absolute top-4 left-4 p-2 bg-white dark:bg-gray-700 shadow hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
  </a>
  <div class="app-container">
    <header class="app-header">
      <h1 id="mainHeader">Studying Deck</h1>
      <p id="deckName">Loading deck name...</p>
      <div id="card-counter" class="text-sm text-gray-500 mt-2"></div>
    </header>

    <main class="flashcard-container">
      <div id="flashcard" class="flashcard">
        <div class="card-front">
          <div class="card-content">
            <h2 id="card-front">Loading...</h2>
            <div id="card-front-image-container" class="card-image-container"></div>
          </div>
        </div>
        <div id="card-back-content" class="card-back hidden">
          <div class="card-content">
            <h2 id="card-back">Loading...</h2>
            <div id="card-back-image-container" class="card-image-container"></div>
          </div>
        </div>
      </div>
    </main>
    
    <div class="stats-container">
        <div class="stat-box">
            <h3>Số lần lặp lại</h3>
            <p id="repetition-count">0</p>
        </div>
        <div class="stat-box">
            <h3>Ôn tập tiếp theo</h3>
            <p id="next-review">-</p>
        </div>
        <div class="stat-box">
            <h3>EF Factor</h3>
            <p id="ef-factor">2.5</p>
        </div>
    </div>

    <div class="controls">
      <div id="remember-level-buttons" class="difficulty-buttons hidden">
        <button id="again" class="difficulty-btn btn-again" data-level="1">Again (1)</button>
        <button id="hard" class="difficulty-btn btn-hard" data-level="2">Hard (2)</button>
        <button id="good" class="difficulty-btn btn-good" data-level="3">Good (3)</button>
        <button id="easy" class="difficulty-btn btn-easy" data-level="4">Easy (4)</button>
      </div>
      <div id="action-buttons" class="action-buttons">
        <button id="flip-btn" class="action-btn btn-flip">Lật Thẻ</button>
      </div>
      <div id="navigation-buttons" class="flex justify-between mt-6">
        <button id="prev-btn" class="action-btn btn-new">Thẻ trước</button>
        <button id="next-btn" class="action-btn btn-new">Thẻ tiếp theo</button>
      </div>
      <div >
        <button id="back-btn" class="action-btn btn-new">Quay về</button>
      </div>
    </div>
  </div>
  
  <script type="module" src="study.js"></script>
</body>
</html>