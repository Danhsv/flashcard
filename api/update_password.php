<?php
// Bắt đầu session để truy cập thông tin phiên làm việc
session_start();
// Nhúng file kết nối database
require_once 'connect.php'; 

// Khởi tạo biến kết nối database
$conn = getConnection();

// Kiểm tra người dùng đã đăng nhập chưa
// Đây là bước bảo mật đầu tiên, nếu không có UserID trong session, người dùng không thể thực hiện hành động này.
if (!isset($_SESSION['UserID'])) {
    header("Location: /api/login.php");
    exit();
}

// Lấy UserID của người dùng hiện tại từ session
$userId = $_SESSION['UserID'];

// Xử lý khi người dùng gửi form đổi mật khẩu
// Kiểm tra xem nút 'update_password' đã được nhấn chưa
if (isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Lấy mật khẩu hiện tại đã được băm từ database để so sánh
    // Sử dụng Prepared Statement để ngăn chặn SQL Injection
    $sql = "SELECT Password FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Kiểm tra xem người dùng có tồn tại và mật khẩu hiện tại có đúng không
    // password_verify() là hàm an toàn để so sánh mật khẩu người dùng nhập với mật khẩu đã băm
    if ($user && password_verify($currentPassword, $user['Password'])) {
        // Mật khẩu hiện tại đúng, tiếp tục kiểm tra mật khẩu mới
        if ($newPassword === $confirmPassword) {
            // Mật khẩu mới và xác nhận mật khẩu khớp nhau
            
            // Băm mật khẩu mới trước khi lưu vào database
            // Điều này đảm bảo mật khẩu không được lưu dưới dạng văn bản thô
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Chuẩn bị câu lệnh SQL để cập nhật mật khẩu
            $updateSql = "UPDATE Users SET Password = ? WHERE UserID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $hashedPassword, $userId);

            // Thực thi câu lệnh cập nhật
            if ($updateStmt->execute()) {
                // In ra thông báo thành công (có thể thay bằng chuyển hướng)
                echo "Cập nhật mật khẩu thành công!";
            } else {
                // Xử lý lỗi nếu có
                echo "Lỗi: Không thể cập nhật mật khẩu.";
            }
            $updateStmt->close();
        } else {
            // Mật khẩu mới không khớp với xác nhận
            echo "Mật khẩu mới và xác nhận mật khẩu không khớp.";
        }
    } else {
        // Mật khẩu hiện tại không đúng
        echo "Mật khẩu hiện tại không đúng.";
    }
}

// Đóng kết nối database
$conn->close();

// Chuyển hướng người dùng trở lại trang đăng nhập sau khi xử lý xong
// Lưu ý: Hành vi này có thể cần điều chỉnh. Tốt hơn nên chuyển hướng về trang cài đặt.
header("Location: /api/login.php");
exit();
?>