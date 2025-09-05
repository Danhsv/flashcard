<?php
// Bắt đầu session để có thể truy cập các biến session.
// Đây là bước bắt buộc trước khi có thể thao tác với session.
session_start();

// Hủy tất cả các biến session. Thao tác này làm trống mảng $_SESSION.
$_SESSION = array();

// Nếu session được lưu trong cookie, cũng hủy cookie session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Đặt lại cookie với thời gian hết hạn trong quá khứ để buộc trình duyệt xóa nó.
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cuối cùng, hủy session trên máy chủ.
session_destroy();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Đăng xuất</title>
    <link rel="stylesheet" href="logout.css">
    <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="content">
                <h2>Flashcard Learning<br /></h2>
                <p>Ôn tập hiệu quả, ghi nhớ lâu dài.</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-box">
                <h1>Đăng xuất thành công</h1>
                <p class="register-text">
                    Bạn đã đăng xuất. <a href="login.php">Đăng nhập lại</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>