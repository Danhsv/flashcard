<?php
// Bắt đầu session và nhúng file kết nối database
session_start();
require_once 'connect.php';

$message = '';
$token = '';
$email = '';
$valid = false;

// Kiểm tra xem có token trong URL không
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $conn = getConnection();
    
    // Tìm người dùng có token hợp lệ và chưa hết hạn
    $sql = "SELECT UserID, Email FROM Users WHERE ResetToken = ? AND TokenExpiry >= NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $email = $user['Email'];
        $valid = true; // Token hợp lệ
    } else {
        $message = "Mã xác thực không hợp lệ hoặc đã hết hạn.";
    }
    $stmt->close();
    $conn->close();
} else {
    $message = "Không tìm thấy mã xác thực.";
}

// Xử lý khi người dùng gửi form đặt lại mật khẩu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'], $_POST['password'], $_POST['confirm_password'])) {
    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới có khớp không
    if ($new_password !== $confirm_password) {
        $message = "Mật khẩu mới không khớp.";
    } else {
        // Kiểm tra token một lần nữa để đảm bảo an toàn
        $conn = getConnection();
        $sql = "SELECT UserID FROM Users WHERE ResetToken = ? AND TokenExpiry >= NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Token hợp lệ, băm mật khẩu mới và cập nhật vào database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE Users SET Password = ?, ResetToken = NULL, TokenExpiry = NULL WHERE ResetToken = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $hashed_password, $token);
            
            if ($stmt_update->execute()) {
                $message = "Mật khẩu của bạn đã được cập nhật thành công.";
            } else {
                $message = "Đã xảy ra lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
            }
            $stmt_update->close();
        } else {
            $message = "Mã xác thực không hợp lệ hoặc đã hết hạn.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body>
<div class="container">
    <div class="right-panel">
        <div class="form-box">
            <h1>Đặt lại mật khẩu</h1>
            <?php if (!empty($message)) echo "<p>$message</p>"; ?>
            
            <?php if ($valid): ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="password" placeholder="Mật khẩu mới" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
                <button type="submit">Đặt lại mật khẩu</button>
            </form>
            <?php endif; ?>

            <p class="back-text">
                <a href="/api/login.php">Quay lại trang đăng nhập</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>