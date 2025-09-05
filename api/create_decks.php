<?php
// Bắt đầu session để lấy UserID của người dùng.
session_start();

// Nhúng file kết nối database.
require_once 'connect.php';

// Lấy đối tượng kết nối từ hàm getConnection().
$conn = getConnection();

// Kiểm tra xem người dùng đã đăng nhập chưa.
if (!isset($_SESSION['UserID'])) {
    // Nếu chưa, trả về lỗi 401 Unauthorized.
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Kiểm tra xem phương thức request có phải là POST không.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu thô từ request body và giải mã JSON.
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Lấy thông tin từ dữ liệu nhận được.
    $deckName = $data['name'];
    $userID = $_SESSION['UserID'];
    $dateOfCreation = date('Y-m-d H:i:s');

    // Chuẩn bị câu lệnh SQL để chèn dữ liệu vào bảng Decks.
    $sql = "INSERT INTO Decks (UserID, DeckName, DateOfCreation) VALUES (?, ?, ?)";
    
    // Sử dụng Prepared Statement để bảo mật, ngăn chặn SQL Injection.
    $stmt = $conn->prepare($sql);
    // Liên kết các tham số với câu lệnh SQL.
    $stmt->bind_param("iss", $userID, $deckName, $dateOfCreation);

    // Thực thi câu lệnh.
    if ($stmt->execute()) {
        // Gửi phản hồi thành công về frontend dưới dạng JSON.
        echo json_encode(['success' => true, 'message' => 'Deck created successfully!']);
    } else {
        // Nếu thực thi thất bại, gửi phản hồi lỗi 500.
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create deck.']);
    }

    // Đóng câu lệnh và kết nối.
    $stmt->close();
    $conn->close();
} else {
    // Nếu không phải phương thức POST, gửi lỗi 405 Method Not Allowed.
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>