<?php
// Thiết lập header để trả về dữ liệu dưới dạng JSON
header('Content-Type: application/json');
// Bắt đầu session để truy cập thông tin người dùng
session_start();
// Nhúng file kết nối database
require_once 'connect.php';

// Gọi hàm getConnection() để lấy đối tượng kết nối
$conn = getConnection();

// Kiểm tra nếu kết nối thất bại
if ($conn === null) {
    http_response_code(500); // Gửi mã lỗi HTTP 500 (Server Error)
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['UserID'])) {
    http_response_code(401); // Gửi mã lỗi HTTP 401 (Unauthorized)
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

// Kiểm tra xem có tham số deck_id được gửi lên không
if (isset($_GET['deck_id'])) {
    $deckId = $_GET['deck_id'];
    $userId = $_SESSION['UserID'];

    // Chuẩn bị câu lệnh SQL để lấy thông tin deck
    // Sử dụng Prepared Statement để ngăn chặn SQL Injection và thêm điều kiện UserID để bảo mật
    $sql = "SELECT DeckName FROM decks WHERE DeckID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    
    // Thêm kiểm tra lỗi cho câu lệnh prepare
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'SQL prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    
    // Gán giá trị vào câu lệnh (i = integer)
    $stmt->bind_param("ii", $deckId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra kết quả
    if ($result->num_rows > 0) {
        $deck = $result->fetch_assoc();
        echo json_encode(['DeckName' => $deck['DeckName']]);
    } else {
        // Nếu không tìm thấy deck hoặc không có quyền truy cập
        http_response_code(404); // Gửi mã lỗi HTTP 404 (Not Found)
        echo json_encode(['error' => 'Deck not found or access denied.']);
    }

    // Đóng kết nối và câu lệnh
    $stmt->close();
    $conn->close();
} else {
    // Nếu thiếu tham số deck_id
    http_response_code(400); // Gửi mã lỗi HTTP 400 (Bad Request)
    echo json_encode(['error' => 'Deck ID not provided.']);
    $conn->close();
}
?>