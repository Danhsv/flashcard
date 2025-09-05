<?php
// Tệp: update_sm2_progress.php
// Chức năng: Cập nhật tiến trình học của một thẻ flashcard dựa trên thuật toán SM-2.

// Bắt đầu session để truy cập UserID của người dùng đã đăng nhập.
session_start();
// Thiết lập header để đảm bảo phản hồi là JSON.
header('Content-Type: application/json; charset=utf-8');

try {
    // Nhúng file kết nối database.
    require_once 'connect.php';
    $conn = getConnection();
    if ($conn === null) {
        // Nếu không thể kết nối database, ném một ngoại lệ.
        throw new Exception('Không thể kết nối đến cơ sở dữ liệu.');
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa.
    if (!isset($_SESSION['UserID'])) {
        // Nếu chưa, trả về mã lỗi 401 (Unauthorized) và thoát.
        http_response_code(401);
        echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    // Lấy dữ liệu từ body của request HTTP (được gửi dưới dạng JSON).
    $data = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra xem dữ liệu JSON có hợp lệ không.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dữ liệu JSON không hợp lệ.');
    }
    
    // Kiểm tra các tham số bắt buộc có tồn tại không.
    if (!isset($data['CardID']) || !isset($data['repetitions']) || !isset($data['easeFactor']) || !isset($data['intervals']) || !isset($data['nextReview'])) {
        throw new Exception('Thiếu tham số bắt buộc.');
    }

    // Gán các giá trị từ JSON vào các biến để dễ sử dụng.
    $cardId = $data['CardID'];
    $repetitions = $data['repetitions'];
    $easeFactor = $data['easeFactor'];
    $intervals = $data['intervals'];
    $nextReview = $data['nextReview'];
    $userId = $_SESSION['UserID'];

    // Chuẩn bị câu lệnh SQL để cập nhật dữ liệu.
    // Mệnh đề WHERE sử dụng cả CardID và UserID để đảm bảo người dùng chỉ có thể cập nhật thẻ của chính họ.
    $sql = "UPDATE flashcards SET repetitions = ?, easeFactor = ?, intervals = ?, nextReview = ? WHERE CardID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Ném ngoại lệ nếu câu lệnh không thể được chuẩn bị.
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    
    // Liên kết các tham số với câu lệnh đã chuẩn bị.
    // "idisii": i (integer) cho repetitions, d (double) cho easeFactor, i (integer) cho intervals, s (string) cho nextReview, i (integer) cho CardID, i (integer) cho UserID.
    $stmt->bind_param("idisii", $repetitions, $easeFactor, $intervals, $nextReview, $cardId, $userId);

    // Thực thi câu lệnh SQL.
    if ($stmt->execute()) {
        // Kiểm tra số hàng bị ảnh hưởng.
        if ($stmt->affected_rows > 0) {
            // Nếu cập nhật thành công, trả về thông báo thành công.
            echo json_encode(['message' => 'Tiến trình học đã được cập nhật.']);
        } else {
            // Nếu không có hàng nào bị ảnh hưởng, có thể là thẻ không tồn tại hoặc không thuộc về người dùng.
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy thẻ để cập nhật hoặc bạn không có quyền.']);
        }
    } else {
        // Ném ngoại lệ nếu có lỗi trong quá trình thực thi.
        throw new Exception('Lỗi thực thi câu lệnh SQL: ' . $stmt->error);
    }

    // Đóng câu lệnh và kết nối database để giải phóng tài nguyên.
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Nếu có bất kỳ ngoại lệ nào xảy ra trong khối try, bắt nó và trả về phản hồi lỗi.
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>