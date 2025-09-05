<?php
// Bắt đầu session để truy cập thông tin người dùng.
session_start();

// Thiết lập header để trả về dữ liệu dưới dạng JSON.
header('Content-Type: application/json; charset=utf-8');

try {
    // Nhúng file kết nối cơ sở dữ liệu.
    require_once 'connect.php';
    $conn = getConnection();
    if ($conn === null) {
        throw new Exception('Không thể kết nối đến cơ sở dữ liệu.');
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa.
    if (!isset($_SESSION['UserID'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    // Kiểm tra và lấy dữ liệu từ yêu cầu POST (FormData).
    if (!isset($_POST['deckId']) || !isset($_POST['front']) || !isset($_POST['back'])) {
        throw new Exception('Dữ liệu không hợp lệ. Vui lòng cung cấp deckId, front và back.');
    }

    $deckId = $_POST['deckId'];
    $front = $_POST['front'];
    $back = $_POST['back'];
    $userId = $_SESSION['UserID'];

    // Thiết lập giá trị mặc định cho RememberLevel.
    $rememberLevel = 0;
    
    // Xử lý tệp hình ảnh
    $imagePath = null;
    // Kiểm tra xem có tệp hình ảnh được tải lên không và không có lỗi
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../image/';
        // Tạo tên tệp duy nhất để tránh trùng lặp
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        // Di chuyển tệp đã tải lên vào thư mục đích
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Lưu đường dẫn tương đối của hình ảnh vào biến
            $imagePath = '/image/' . $fileName;
        } else {
            throw new Exception('Lỗi khi tải lên hình ảnh.');
        }
    }

    // Chuẩn bị câu lệnh SQL với tên cột Image mới.
    $sql = "INSERT INTO flashcards (deckID, Front_text, Back_text, RememberLevel, UserID, Image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    
    // Liên kết các tham số và thực thi.
    // 'issiis' đại diện cho kiểu dữ liệu: i (int), s (string), s (string), i (int), i (int), s (string)
    $stmt->bind_param("issiis", $deckId, $front, $back, $rememberLevel, $userId, $imagePath);

    if ($stmt->execute()) {
        // Gửi phản hồi JSON khi thành công, bao gồm ID của thẻ vừa tạo
        echo json_encode(['message' => 'Thẻ đã được thêm thành công.', 'cardId' => $conn->insert_id]);
    } else {
        throw new Exception('Lỗi thực thi câu lệnh SQL: ' . $stmt->error);
    }

    // Đóng câu lệnh và kết nối
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Trả về phản hồi JSON với mã lỗi 500 nếu có lỗi xảy ra
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>