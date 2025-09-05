<?php
// Bắt đầu session để lấy UserID
session_start();

// Nhúng file kết nối database
require_once 'connect.php';

// Lấy đối tượng kết nối từ hàm getConnection()
$conn = getConnection();

// Kiểm tra xem UserID đã được thiết lập trong session chưa
if (!isset($_SESSION['UserID'])) {
    http_response_code(401); // Gửi mã lỗi HTTP 401 (Unauthorized)
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Kiểm tra xem phương thức request có phải là POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ JavaScript (dữ liệu được gửi dưới dạng JSON)
    $data = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra xem dữ liệu JSON có hợp lệ không
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Gửi mã lỗi 400 (Bad Request)
        echo json_encode(['error' => 'Invalid JSON data received.']);
        exit();
    }

    $deckId = $data['id'] ?? null;
    $newDeckName = $data['name'] ?? '';
    $userID = $_SESSION['UserID'];

    // Kiểm tra xem các tham số bắt buộc có tồn tại không
    if (empty($deckId) || empty($newDeckName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Deck ID and new name are required.']);
        exit();
    }

    // Chuẩn bị câu lệnh SQL để cập nhật tên deck
    // Sẽ cập nhật chỉ khi deckID và UserID khớp để đảm bảo bảo mật
    $sql = "UPDATE Decks SET DeckName = ? WHERE DeckID = ? AND UserID = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500); // Lỗi máy chủ
        echo json_encode(['error' => 'Failed to prepare statement.', 'details' => $conn->error]);
        exit();
    }
    
    // "sii" cho tham số: string (newDeckName), integer (deckId), integer (userID)
    $stmt->bind_param("sii", $newDeckName, $deckId, $userID);

    if ($stmt->execute()) {
        // Kiểm tra số hàng bị ảnh hưởng để biết liệu có bản ghi nào được cập nhật hay không
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Deck renamed successfully!']);
        } else {
            // Trường hợp không có hàng nào được cập nhật có thể do deck không tồn tại
            // hoặc người dùng không có quyền chỉnh sửa
            http_response_code(404); // Gửi mã lỗi 404 (Not Found)
            echo json_encode(['error' => 'Deck not found or you do not have permission to rename it.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to execute statement.', 'details' => $stmt->error]);
    }

    // Đóng câu lệnh và kết nối
    $stmt->close();
    $conn->close();

} else {
    // Nếu không phải phương thức POST, gửi lỗi
    http_response_code(405); // Gửi mã lỗi 405 (Method Not Allowed)
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>