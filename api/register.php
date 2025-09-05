<?php
// Nhúng file kết nối database để có thể tương tác với cơ sở dữ liệu.
// File 'connect.php' chứa các thông tin và hàm cần thiết để kết nối đến MySQL.
require_once 'connect.php';

// Khởi tạo biến kết nối database bằng cách gọi hàm getConnection() từ 'connect.php'.
$conn = getConnection();

// Khởi tạo biến $error_message để lưu trữ các thông báo lỗi, ban đầu là rỗng.
$error_message = '';

// Kiểm tra nếu yêu cầu được gửi bằng phương thức POST (tức là người dùng đã nhấn nút "Đăng ký").
// Điều kiện này đảm bảo rằng code xử lý chỉ chạy khi form được gửi đi.
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form và làm sạch chúng bằng htmlspecialchars().
    // htmlspecialchars() giúp ngăn chặn các cuộc tấn công XSS (Cross-Site Scripting)
    // bằng cách chuyển đổi các ký tự đặc biệt thành các thực thể HTML.
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 
    $major = htmlspecialchars($_POST['major']);
    
    // Xác định vai trò của người dùng.
    // Kiểm tra tổng số người dùng hiện có trong cơ sở dữ liệu.
    $check_total_users_sql = "SELECT COUNT(*) as total FROM users";
    $check_total_users_result = $conn->query($check_total_users_sql);
    $row = $check_total_users_result->fetch_assoc();
    if ($row['total'] == 0) {
        // Nếu là người dùng đầu tiên đăng ký, gán vai trò 'admin'.
        $role = 'admin';
    } else {
        // Nếu không, gán vai trò mặc định là 'user'.
        $role = 'user';
    }


    // Thêm các kiểm tra xác thực dữ liệu.
    // 1. Kiểm tra xem mật khẩu và mật khẩu xác nhận có khớp nhau không.
    if ($password !== $confirm_password) {
        $error_message = "Lỗi: Mật khẩu xác nhận không khớp.";
    } 
    // 2. Kiểm tra xem các trường bắt buộc có bị trống không.
    elseif (empty($username) || empty($email) || empty($password)) {
        $error_message = "Lỗi: Vui lòng điền đầy đủ các trường bắt buộc.";
    } 
    else {
        // Nếu các kiểm tra ban đầu đều OK, tiến hành kiểm tra database.
        
        // 3. KIỂM TRA XEM TÊN ĐĂNG NHẬP ĐÃ TỒN TẠI CHƯA.
        // Sử dụng Prepared Statement để ngăn chặn tấn công SQL Injection.
        $check_sql = "SELECT UserID FROM users WHERE Username = ?";
        $check_stmt = $conn->prepare($check_sql);
        // Liên kết tham số 's' (string) với biến $username.
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Nếu có kết quả trả về, tên đăng nhập đã tồn tại.
            $error_message = "Lỗi: Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
        } else {
            // 4. KIỂM TRA XEM EMAIL ĐÃ TỒN TẠI CHƯA.
            // Tương tự, sử dụng Prepared Statement để kiểm tra email.
            $check_email_sql = "SELECT UserID FROM users WHERE Email = ?";
            $check_email_stmt = $conn->prepare($check_email_sql);
            $check_email_stmt->bind_param("s", $email);
            $check_email_stmt->execute();
            $check_email_result = $check_email_stmt->get_result();

            if ($check_email_result->num_rows > 0) {
                // Nếu có kết quả trả về, email đã tồn tại.
                $error_message = "Lỗi: Email đã tồn tại. Vui lòng sử dụng email khác.";
            } else {
                // 5. Nếu không có lỗi trùng lặp, tiến hành thêm người dùng vào database.
                // Băm mật khẩu bằng password_hash(). Đây là phương pháp an toàn nhất để lưu trữ mật khẩu.
                // Nó tạo ra một chuỗi băm không thể đảo ngược.
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $dateOfCreation = date('Y-m-d H:i:s');
                
                // Chuẩn bị câu lệnh SQL INSERT.
                $sql = "INSERT INTO users (Username, Password, Email, Major, Role, DateofCreation) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                // Liên kết các tham số: 'ssssss' tương ứng với 6 chuỗi.
                $stmt->bind_param("ssssss", $username, $hashed_password, $email, $major, $role, $dateOfCreation);

                // Thực thi câu lệnh.
                if ($stmt->execute()) {
                    // Nếu việc chèn dữ liệu thành công, chuyển hướng người dùng đến trang đăng nhập
                    // với một tham số để hiển thị thông báo thành công.
                    header("Location: login.php?registration_success=1");
                    exit(); // Dừng việc thực thi script sau khi chuyển hướng.
                } else {
                    // Nếu có lỗi khi thực thi, lưu thông báo lỗi.
                    $error_message = "Lỗi: " . $stmt->error;
                }
                $stmt->close();
            }
            $check_email_stmt->close();
        }
        $check_stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="register.css">
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
        <h1>Đăng ký</h1>
        <?php 
        // Hiển thị thông báo lỗi nếu biến $error_message không rỗng.
        if (!empty($error_message)) { 
        ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php 
        } 
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
          <input type="text" name="username" placeholder="Tên đăng nhập" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Mật khẩu" required>
          <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
          <input type="text" name="major" placeholder="Chuyên ngành" required>
          <button type="submit">Đăng ký</button>
        </form>
        <p class="register-text">
          Đã có tài khoản? <a href="/api/login.php">Đăng nhập ngay</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>