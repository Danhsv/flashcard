<?php
// Bắt đầu session để truy cập thông tin người dùng đã đăng nhập.
session_start();
// Nhúng file kết nối database
require_once 'connect.php';

$success_message = "";
// Kiểm tra nếu người dùng vừa được chuyển hướng từ trang đăng ký thành công
if (isset($_GET['registration_success']) && $_GET['registration_success'] == 1) {
    $success_message = "Đăng ký thành công! Vui lòng đăng nhập.";
}

// Khởi tạo biến thông báo lỗi
$error_message = "";

// Lấy đối tượng kết nối
$conn = getConnection();
if ($conn === null) {
    // Nếu kết nối thất bại, xử lý lỗi và thoát
    die("Lỗi kết nối đến cơ sở dữ liệu.");
}

// Kiểm tra xem yêu cầu có phải là POST (do người dùng gửi form) không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form và làm sạch tên đăng nhập
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Chuẩn bị câu lệnh SQL để lấy thông tin người dùng
    // Sử dụng Prepared Statement để ngăn chặn SQL Injection
    $sql = "SELECT UserID, Password, Email FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // "s" là kiểu string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Nếu tìm thấy một người dùng khớp
        $user = $result->fetch_assoc();
        // Xác minh mật khẩu đã băm bằng password_verify()
        if (password_verify($password, $user['Password'])) {
            // Đăng nhập thành công, thiết lập biến session
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $username;
            $_SESSION['Email'] = $user['Email'];
            // Chuyển hướng người dùng đến trang dashboard
            header("Location: /dashboard.php");
            exit(); // Ngừng thực thi script
        } else {
            // Mật khẩu không chính xác
            $error_message = "Người dùng nhập sai tên hoặc mật khẩu.";
        }
    } else {
        // Tên người dùng không tồn tại
        $error_message = "Người dùng nhập sai tên hoặc mật khẩu.";
    }

    // Đóng câu lệnh và kết nối
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/png" href="flashcard.png">
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="content">
        <h2>Flashcard Learning<br></h2>
        <p>Ôn tập hiệu quả, ghi nhớ lâu dài.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="form-box">
        <h1>Đăng nhập</h1>
        <?php 
            // Hiển thị thông báo đăng ký thành công nếu có
            if (!empty($success_message)) { ?>
          <p style="color: green;"><?php echo $success_message; ?></p>
        <?php } ?>
        <?php 
            // Hiển thị thông báo lỗi nếu có
            if (!empty($error_message)) { ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php } ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
          <input type="text" name="username" placeholder="Tên đăng nhập" required>
          <input type="password" name="password" placeholder="Mật khẩu" required>
          <button type="submit">Đăng nhập</button>
        </form>
        <p class="register-text">
          Chưa có tài khoản? <a href="/api/register.php">Đăng ký ngay</a>
        </p>
        <p class="register-text">
          <a href="/api/forgot_password.php">Quên mật khẩu?</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>