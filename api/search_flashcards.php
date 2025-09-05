<?php
// Bắt đầu session để có thể truy cập các biến session, đặc biệt là UserID.
session_start();
// Thiết lập header để trả về dữ liệu dưới dạng JSON.
// Điều này giúp trình duyệt và các ứng dụng frontend hiểu được định dạng dữ liệu trả về.
header('Content-Type: application/json; charset=utf-8');

try {
    // Nhúng file kết nối cơ sở dữ liệu.
    require_once 'connect.php';
    $conn = getConnection();
    // Kiểm tra xem kết nối có thành công không.
    if ($conn === null) {
        throw new Exception('Không thể kết nối đến cơ sở dữ liệu.');
    }

    // Kiểm tra xem người dùng đã đăng nhập chưa bằng cách kiểm tra biến session UserID.
    if (!isset($_SESSION['UserID'])) {
        // Nếu chưa đăng nhập, trả về mã lỗi 401 (Unauthorized) và thông báo lỗi.
        http_response_code(401);
        echo json_encode(['error' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    // Kiểm tra xem có tham số tìm kiếm 'q' được gửi qua URL không.
    if (!isset($_GET['q'])) {
        // Nếu thiếu, trả về mã lỗi 400 (Bad Request).
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu tham số tìm kiếm.']);
        exit();
    }

    $userId = $_SESSION['UserID'];
    // Thêm ký tự '%' vào đầu và cuối chuỗi tìm kiếm để sử dụng với toán tử LIKE trong SQL.
    // Điều này cho phép tìm kiếm các từ khóa ở bất kỳ vị trí nào trong văn bản.
    $searchQuery = '%' . $_GET['q'] . '%';

    // Chuẩn bị câu lệnh SQL.
    // - Sử dụng JOIN để kết hợp bảng 'flashcards' và 'decks' nhằm lấy cả tên deck.
    // - Mệnh đề WHERE f.UserID = ? đảm bảo chỉ tìm kiếm trong thẻ của người dùng hiện tại.
    // - Mệnh đề OR cho phép tìm kiếm từ khóa trong cả hai cột Front_text và Back_text.
    $sql = "SELECT f.Front_text, f.Back_text, f.Image, d.DeckName 
            FROM flashcards f
            JOIN decks d ON f.DeckID = d.DeckID
            WHERE f.UserID = ? AND (f.Front_text LIKE ? OR f.Back_text LIKE ?)";

    // Chuẩn bị câu lệnh để ngăn chặn SQL Injection.
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }

    // Liên kết các tham số: 'i' cho integer (userId), 'ss' cho hai chuỗi (searchQuery).
    $stmt->bind_param("iss", $userId, $searchQuery, $searchQuery);
    // Thực thi câu lệnh.
    $stmt->execute();
    // Lấy kết quả từ câu lệnh đã thực thi.
    $result = $stmt->get_result();

    $cards = [];
    // Lặp qua các hàng dữ liệu và thêm chúng vào một mảng.
    while ($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }

    // Mã hóa mảng thành chuỗi JSON và gửi về.
    echo json_encode($cards);
    
    // Đóng câu lệnh và kết nối để giải phóng tài nguyên.
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Nếu có bất kỳ ngoại lệ nào xảy ra, trả về mã lỗi 500 (Server Error) và thông báo lỗi.
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>