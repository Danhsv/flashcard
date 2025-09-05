<?php
// Bắt đầu session để lấy UserID
session_start();
// Thiết lập header phản hồi là JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Nhúng file kết nối cơ sở dữ liệu
    require_once 'connect.php';
    $conn = getConnection();
    if ($conn === null) {
        throw new Exception('Không thể kết nối đến cơ sở dữ liệu.');
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa
    if (!isset($_SESSION['UserID'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    // Lấy dữ liệu CardID từ yêu cầu JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['CardID'])) {
        throw new Exception('Thiếu tham số CardID.');
    }

    $cardId = $data['CardID'];
    $userId = $_SESSION['UserID'];

    // Bắt đầu một giao dịch để đảm bảo tính toàn vẹn của dữ liệu
    $conn->begin_transaction();

    // 1. Xóa tất cả dữ liệu tiến độ học tập liên quan đến thẻ
    $sqlDeleteProgress = "DELETE FROM study_progress WHERE CardID = ?";
    $stmtProgress = $conn->prepare($sqlDeleteProgress);
    if ($stmtProgress === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    $stmtProgress->bind_param("i", $cardId);
    $stmtProgress->execute();
    $stmtProgress->close();

    // 2. Lấy đường dẫn hình ảnh của thẻ để xóa tệp vật lý
    $sqlGetImage = "SELECT Image FROM flashcards WHERE CardID = ? AND UserID = ?";
    $stmtImage = $conn->prepare($sqlGetImage);
    $stmtImage->bind_param("ii", $cardId, $userId);
    $stmtImage->execute();
    $resultImage = $stmtImage->get_result();
    $imageData = $resultImage->fetch_assoc();
    $stmtImage->close();

    // 3. Xóa thẻ khỏi bảng flashcards
    $sqlDeleteCard = "DELETE FROM flashcards WHERE CardID = ? AND UserID = ?";
    $stmtCard = $conn->prepare($sqlDeleteCard);
    if ($stmtCard === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    $stmtCard->bind_param("ii", $cardId, $userId);
    $stmtCard->execute();

    if ($stmtCard->affected_rows > 0) {
        // Nếu thẻ đã được xóa thành công, tiến hành xóa tệp hình ảnh
        if ($imageData && !empty($imageData['Image'])) {
            $imagePath = '../' . $imageData['Image'];
            // Kiểm tra tệp có tồn tại trước khi xóa
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        // Gửi phản hồi thành công và xác nhận giao dịch
        $conn->commit();
        echo json_encode(['message' => 'Thẻ đã được xóa thành công.']);
    } else {
        // Nếu không tìm thấy thẻ, hoàn tác giao dịch
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['error' => 'Không tìm thấy thẻ để xóa hoặc bạn không có quyền.']);
    }
    $stmtCard->close();
    $conn->close();

} catch (Exception $e) {
    // Nếu có lỗi, hoàn tác giao dịch nếu nó đã được bắt đầu
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    // Trả về lỗi 500
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>