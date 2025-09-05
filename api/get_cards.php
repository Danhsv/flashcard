<?php
// Bắt đầu session để truy cập thông tin người dùng đã đăng nhập.
session_start();

// Thiết lập header để trả về dữ liệu dưới dạng JSON.
header('Content-Type: application/json; charset=utf-8');

try {
    // Nhúng file kết nối cơ sở dữ liệu.
    require_once 'connect.php';
    $conn = getConnection();
    if ($conn === null) {
        throw new Exception('Không thể kết nối đến cơ sở dữ liệu. Vui lòng đảm bảo MySQL đang chạy.');
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa.
    if (!isset($_SESSION['UserID'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    $user_id = $_SESSION['UserID'];

    // Lấy deck_id từ request GET.
    if (!isset($_GET['deck_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu tham số deck_id.']);
        exit();
    }

    $deckId = $_GET['deck_id'];

    // Chuẩn bị câu lệnh SQL để lấy các thẻ flashcard thuộc về deck của người dùng.
    $sql = "SELECT CardID, Front_text, Back_text, RememberLevel, repetitions, easeFactor, intervals, nextReview, DeckID, Image FROM flashcards WHERE DeckID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    
    // Liên kết tham số và thực thi câu lệnh.
    // "ii" đại diện cho hai tham số số nguyên (integer).
    $stmt->bind_param("ii", $deckId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = [];
    if ($result->num_rows > 0) {
        // Lặp qua các hàng dữ liệu và thêm vào mảng.
        while ($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }
    }
    
    // Đóng câu lệnh và kết nối.
    $stmt->close();
    $conn->close();

    // Trả về dữ liệu thẻ dưới dạng JSON.
    echo json_encode($cards);

} catch (Exception $e) {
    // Trả về lỗi 500 nếu có ngoại lệ.
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>