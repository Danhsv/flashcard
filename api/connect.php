<?php
/**
 * Tạo và trả về một đối tượng kết nối đến cơ sở dữ liệu.
 * @return mysqli|null Đối tượng kết nối nếu thành công, hoặc null nếu thất bại.
 */
function getConnection() {
    // Tên máy chủ cơ sở dữ liệu (thường là "localhost" cho môi trường cục bộ).
    $servername = "localhost";
    // Tên người dùng MySQL.
    $username = "root";
    // Mật khẩu của người dùng MySQL. Mật khẩu rỗng là phổ biến cho môi trường phát triển.
    $password = "";
    // Tên cơ sở dữ liệu.
    $dbname = "flashcard";

    // Tạo một đối tượng kết nối mới.
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra xem kết nối có lỗi không.
    if ($conn->connect_error) {
        // Nếu có lỗi, trả về null để báo hiệu thất bại.
        return null;
    }

    // Nếu kết nối thành công, trả về đối tượng kết nối.
    return $conn;
}
?>