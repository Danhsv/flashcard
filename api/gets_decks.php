<?php
// Bắt đầu session để truy cập UserID
session_start();
// Thiết lập header phản hồi là JSON
header('Content-Type: application/json');
// Nhúng file kết nối database
require_once 'connect.php';

// Gọi hàm getConnection() để lấy đối tượng kết nối
$conn = getConnection();

// Kiểm tra nếu kết nối thất bại
if ($conn === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể kết nối đến cơ sở dữ liệu.']);
    exit();
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userID = $_SESSION['UserID'];
// Chuẩn bị câu lệnh SQL để lấy tất cả các deck của người dùng hiện tại
$sql = "SELECT DeckID, DeckName, description, DateOfCreation FROM Decks WHERE UserID = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi chuẩn bị câu lệnh SQL.']);
    exit();
}

// Liên kết tham số UserID (kiểu integer) vào câu lệnh
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$decks = [];
// Lặp qua các hàng kết quả và thêm vào mảng
while ($row = $result->fetch_assoc()) {
    $decks[] = [
        'id' => $row['DeckID'],
        'name' => $row['DeckName'],
        'description' => $row['description'],
        'date' => $row['DateOfCreation']
    ];
}

// Trả về mảng decks dưới dạng JSON
echo json_encode($decks);

// Đóng câu lệnh và kết nối
$stmt->close();
$conn->close();
?>