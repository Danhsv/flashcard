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
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    // Lấy dữ liệu deckId từ yêu cầu JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dữ liệu JSON không hợp lệ.');
    }

    $deckId = $data['id'] ?? null;
    $userID = $_SESSION['UserID'];

    if (empty($deckId)) {
        throw new Exception('Deck ID is required.');
    }

    // Bắt đầu một giao dịch để đảm bảo tính toàn vẹn của dữ liệu
    $conn->begin_transaction();

    // 1. Lấy tất cả các CardID thuộc về deck này
    $sqlGetCardIds = "SELECT CardID FROM flashcards WHERE DeckID = ?";
    $stmtGetCards = $conn->prepare($sqlGetCardIds);
    $stmtGetCards->bind_param("i", $deckId);
    $stmtGetCards->execute();
    $result = $stmtGetCards->get_result();
    $cardIds = [];
    while ($row = $result->fetch_assoc()) {
        $cardIds[] = $row['CardID'];
    }
    $stmtGetCards->close();

    // 2. Xóa tất cả tiến độ học tập liên quan
    if (!empty($cardIds)) {
        // Tạo chuỗi placeholders để sử dụng trong câu lệnh IN
        $placeholders = implode(',', array_fill(0, count($cardIds), '?'));
        $sqlDeleteProgress = "DELETE FROM study_progress WHERE CardID IN ($placeholders)";
        $stmtDeleteProgress = $conn->prepare($sqlDeleteProgress);
        if ($stmtDeleteProgress === false) {
            throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
        }
        // Liên kết các tham số một cách động
        $types = str_repeat('i', count($cardIds));
        $stmtDeleteProgress->bind_param($types, ...$cardIds);
        $stmtDeleteProgress->execute();
        $stmtDeleteProgress->close();
    }
    
    // 3. Xóa tất cả các thẻ flashcard thuộc về deck này
    $sqlDeleteCards = "DELETE FROM flashcards WHERE DeckID = ?";
    $stmtDeleteCards = $conn->prepare($sqlDeleteCards);
    if ($stmtDeleteCards === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    $stmtDeleteCards->bind_param("i", $deckId);
    $stmtDeleteCards->execute();
    $stmtDeleteCards->close();

    // 4. Cuối cùng, xóa deck
    $sqlDeleteDeck = "DELETE FROM Decks WHERE DeckID = ? AND UserID = ?";
    $stmtDeleteDeck = $conn->prepare($sqlDeleteDeck);
    if ($stmtDeleteDeck === false) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }
    $stmtDeleteDeck->bind_param("ii", $deckId, $userID);
    $stmtDeleteDeck->execute();

    if ($stmtDeleteDeck->affected_rows > 0) {
        // Gửi phản hồi thành công và xác nhận giao dịch
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Deck và tất cả thẻ liên quan đã được xóa thành công.']);
    } else {
        // Nếu không tìm thấy deck, hoàn tác giao dịch
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['error' => 'Không tìm thấy Deck hoặc bạn không có quyền xóa nó.']);
    }

    $stmtDeleteDeck->close();
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