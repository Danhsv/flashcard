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

    // Lấy dữ liệu từ yêu cầu POST.
    $cardId = $_POST['cardId'] ?? null;
    $front = $_POST['front'] ?? null;
    $back = $_POST['back'] ?? null;
    $userId = $_SESSION['UserID'];

    if (!$cardId || !$front || !$back) {
        throw new Exception('Dữ liệu không hợp lệ. Vui lòng cung cấp cardId, front và back.');
    }

    $imagePath = null;
    // Kiểm tra xem có tệp hình ảnh mới được tải lên không.
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Lấy đường dẫn hình ảnh cũ để xóa.
        $sqlOldImage = "SELECT Image FROM flashcards WHERE CardID = ? AND UserID = ?";
        $stmtOldImage = $conn->prepare($sqlOldImage);
        $stmtOldImage->bind_param("ii", $cardId, $userId);
        $stmtOldImage->execute();
        $result = $stmtOldImage->get_result();
        $oldImagePath = $result->fetch_assoc()['Image'] ?? null;
        $stmtOldImage->close();

        // Xóa hình ảnh cũ nếu tồn tại.
        if ($oldImagePath && file_exists('../' . $oldImagePath)) {
            unlink('../' . $oldImagePath);
        }

        // Tải lên hình ảnh mới.
        $uploadDir = '../image/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = '/image/' . $fileName;
        } else {
            throw new Exception('Lỗi khi tải lên hình ảnh mới.');
        }
    }
    
    // Chuẩn bị câu lệnh SQL để cập nhật thẻ một cách linh hoạt.
    $sql = "UPDATE flashcards SET Front_text = ?, Back_text = ?";
    $params = [$front, $back];
    $types = "ss";

    // Nếu có hình ảnh mới, thêm cột Image vào câu lệnh UPDATE.
    if ($imagePath !== null) {
        $sql .= ", Image = ?";
        $params[] = $imagePath;
        $types .= "s";
    }

    // Thêm điều kiện WHERE để đảm bảo chỉ cập nhật thẻ của người dùng hiện tại.
    $sql .= " WHERE CardID = ? AND UserID = ?";
    $params[] = $cardId;
    $params[] = $userId;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    
    // Liên kết các tham số và thực thi.
    // Dấu ... mở rộng mảng $params thành các tham số riêng lẻ cho hàm bind_param.
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Kiểm tra xem có hàng nào bị ảnh hưởng không.
        if ($imagePath !== null || $stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Thẻ đã được cập nhật thành công.']);
        } else {
            // Trường hợp không có gì thay đổi, trả về lỗi 404.
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy thẻ hoặc không có gì thay đổi.']);
        }
    } else {
        throw new Exception('Lỗi thực thi câu lệnh SQL: ' . $stmt->error);
    }
    
    // Đóng câu lệnh và kết nối.
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Trả về lỗi 500 nếu có ngoại lệ.
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>