<?php
// Bắt đầu session để sử dụng các biến phiên làm việc (nếu cần, mặc dù không được sử dụng trực tiếp ở đây)
session_start();
// Nhúng file kết nối database
require_once 'connect.php';

// Khởi tạo các biến để kiểm soát trạng thái giao diện người dùng
$message = '';
$show_password_form = false;
$password_reset_success = false;
$user_id_to_reset = null;

// Lấy đối tượng kết nối
$conn = getConnection();
// Kiểm tra nếu kết nối thất bại, hiển thị lỗi và thoát
if ($conn === null) {
    die("Lỗi kết nối đến cơ sở dữ liệu.");
}

// Xử lý khi người dùng gửi form nhập tên đăng nhập và email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'verify') {
    // Làm sạch dữ liệu đầu vào để ngăn chặn tấn công XSS
    $username = htmlspecialchars($_POST['username']);
    // Lọc email để đảm bảo tính hợp lệ
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!empty($username) && !empty($email)) {
        // Chuẩn bị câu lệnh SQL để kiểm tra cả tên đăng nhập và email
        // Sử dụng Prepared Statement để ngăn chặn SQL Injection
        $sql = "SELECT UserID FROM Users WHERE Username = ? AND Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Tên đăng nhập và email khớp, chuyển sang bước nhập mật khẩu mới
            $user = $result->fetch_assoc();
            $user_id_to_reset = $user['UserID'];
            $show_password_form = true;
            $message = "Vui lòng nhập mật khẩu mới của bạn.";
        } else {
            $message = "Tên đăng nhập hoặc địa chỉ email không chính xác.";
        }
        $stmt->close();
    } else {
        $message = "Vui lòng nhập đầy đủ tên đăng nhập và địa chỉ email của bạn.";
    }
}

// Xử lý khi người dùng gửi form đặt lại mật khẩu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reset') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra xem mật khẩu mới và xác nhận mật khẩu có khớp nhau không
    if ($new_password !== $confirm_password) {
        $message = "Mật khẩu mới không khớp.";
        // Để hiển thị lại form mật khẩu, bạn cần lưu lại user_id
        $show_password_form = true;
        $user_id_to_reset = $user_id;
    } else {
        // Băm mật khẩu mới bằng thuật toán an toàn
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // Chuẩn bị câu lệnh SQL để cập nhật mật khẩu
        $sql_update = "UPDATE Users SET Password = ? WHERE UserID = ?";
        $stmt_update = $conn->prepare($sql_update);
        // 'si' = string, integer
        $stmt_update->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt_update->execute()) {
            $message = "Mật khẩu của bạn đã được cập nhật thành công.";
            // Sau khi cập nhật, ẩn form mật khẩu và các form khác
            $show_password_form = false;
            $password_reset_success = true;
        } else {
            $message = "Đã xảy ra lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
        }
        $stmt_update->close();
    }
}

// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body>
<div class="container">
    <div class="right-panel">
        <div class="form-box">
            <h1>Quên mật khẩu</h1>
            <?php 
                // Hiển thị thông báo nếu có
                if (!empty($message)) echo "<p>$message</p>"; 
            ?>

            <?php if (!$password_reset_success): ?>
                <?php if (!$show_password_form): ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <input type="hidden" name="action" value="verify">
                    <input type="text" name="username" placeholder="Tên đăng nhập" required>
                    <input type="email" name="email" placeholder="Email đăng ký" required>
                    <button type="submit">Xác thực</button>
                </form>
                <?php else: ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="user_id" value="<?php echo $user_id_to_reset; ?>">
                    <input type="password" name="password" placeholder="Mật khẩu mới" required>
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
                    <button type="submit">Đặt lại mật khẩu</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>

            <p class="register-text">
                <a href="login.php">Quay lại trang đăng nhập</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>