<?php
// Bắt đầu session và kết nối database
session_start();
require_once 'connect.php'; // Nhúng file kết nối database

// Lấy đối tượng kết nối từ hàm getConnection()
$conn = getConnection();

// Kiểm tra người dùng đã đăng nhập chưa
// Đây là bước bảo mật cơ bản để đảm bảo chỉ người dùng đã xác thực mới có quyền truy cập.
if (!isset($_SESSION['UserID'])) {
    header("Location: /api/login.php");
    exit(); // Ngăn chặn script tiếp tục thực thi
}

// Lấy UserID của người dùng hiện tại từ session
$userId = $_SESSION['UserID'];

// --- Xử lý khi người dùng gửi form đổi tên ---
if (isset($_POST['update_username'])) {
    // Lấy tên người dùng mới từ form và làm sạch dữ liệu
    // htmlspecialchars() giúp ngăn chặn các cuộc tấn công XSS
    $newUsername = htmlspecialchars($_POST['new_username']);

    // Kiểm tra tên người dùng mới không được rỗng
    if (!empty($newUsername)) {
        // Chuẩn bị câu lệnh SQL để cập nhật tên người dùng
        // Sử dụng Prepared Statement để ngăn chặn SQL Injection
        $sql = "UPDATE Users SET Username = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        
        // Liên kết tham số: 's' (string) cho tên, 'i' (integer) cho ID
        $stmt->bind_param("si", $newUsername, $userId);
        
        // Thực thi câu lệnh cập nhật
        if ($stmt->execute()) {
            // Cập nhật tên trong session để giao diện người dùng được cập nhật ngay lập tức
            $_SESSION['Username'] = $newUsername;
            echo "Cập nhật tên người dùng thành công!";
        } else {
            // Xử lý lỗi nếu việc cập nhật thất bại
            echo "Lỗi: Không thể cập nhật tên người dùng.";
        }
        $stmt->close();
    } else {
        echo "Tên người dùng không được rỗng.";
    }
}

// --- Xử lý khi người dùng gửi form đổi email ---
if (isset($_POST['update_email'])) {
    // Lấy email mới từ form và làm sạch dữ liệu
    $newEmail = htmlspecialchars($_POST['new_email']);

    // Kiểm tra email mới có hợp lệ không bằng hàm filter_var()
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        // Chuẩn bị câu lệnh SQL để cập nhật email
        $sql = "UPDATE Users SET Email = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);

        // Liên kết tham số: 's' (string) cho email, 'i' (integer) cho ID
        $stmt->bind_param("si", $newEmail, $userId);

        // Thực thi câu lệnh cập nhật
        if ($stmt->execute()) {
            // Cập nhật email trong session
            $_SESSION['Email'] = $newEmail;
            echo "Cập nhật email thành công!";
        } else {
            echo "Lỗi: Không thể cập nhật email.";
        }
        $stmt->close();
    } else {
        echo "Email không hợp lệ.";
    }
}

// Đóng kết nối database sau khi hoàn tất các thao tác
$conn->close();

// Chuyển hướng người dùng trở lại trang cài đặt sau khi xử lý xong
// Điều này giúp người dùng nhìn thấy kết quả cập nhật ngay trên trang settings
header("Location: /api/settings.php");
exit(); // Ngăn chặn script tiếp tục thực thi sau khi chuyển hướng
?>