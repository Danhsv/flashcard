<?php
// Bắt đầu một session để truy cập thông tin phiên làm việc.
// Điều này rất quan trọng để xác định người dùng đã đăng nhập.
session_start();
// Nhúng file kết nối database, nơi chứa hàm getConnection().
require_once 'connect.php';

// Lấy đối tượng kết nối từ hàm getConnection().
$conn = getConnection();

// Kiểm tra xem biến session 'UserID' có tồn tại không.
// Đây là bước kiểm tra bảo mật cơ bản để đảm bảo chỉ người dùng đã đăng nhập mới có thể truy cập.
if (!isset($_SESSION['UserID'])) {
    // Nếu không có UserID trong session, chuyển hướng người dùng đến trang đăng nhập.
    header("Location: /api/login.php");
    exit(); // Dừng việc thực thi script.
}

// Lấy UserID từ session để truy vấn thông tin người dùng.
$userId = $_SESSION['UserID'];

// Truy vấn thông tin người dùng từ database.
// Sử dụng Prepared Statement để ngăn chặn SQL Injection.
$sql = "SELECT Username, Email FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
// Liên kết tham số 'i' (integer) với biến $userId.
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$username = '';
$email = '';

// Kiểm tra xem có kết quả trả về không.
if ($result->num_rows === 1) {
    // Lấy thông tin người dùng từ kết quả truy vấn.
    $user = $result->fetch_assoc();
    $username = $user['Username'];
    $email = $user['Email'];
} else {
    // Trường hợp không tìm thấy người dùng (ví dụ: UserID trong session không hợp lệ).
    // Có thể chuyển hướng người dùng đến trang đăng xuất để xóa session và yêu cầu họ đăng nhập lại.
    header("Location: /api/logout.php");
    exit();
}

// Đóng câu lệnh và kết nối để giải phóng tài nguyên.
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="vi" class="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cài đặt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="settings.css">
    <style>
      /* Hiệu ứng chuyển động cho nút chuyển đổi Dark Mode */
      .dot {
        transition: transform 0.3s ease-in-out;
      }
      .dark #theme-toggle:checked ~ .dot {
        transform: translateX(24px);
        background: #2563eb;
      }
      
      /* CSS cho phần có thể thu gọn */
      .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
      }
      .collapsible-content.expanded {
        max-height: 500px; /* Giá trị lớn hơn nội dung để đảm bảo hiển thị hết */
      }
      .rotate-180 {
        transform: rotate(180deg);
        transition: transform 0.3s ease-in-out;
      }

      /* Style cho nút trở về với viền tròn */
      a.back-button {
          border-radius: 9999px;
          border: 1px solid rgba(0, 0, 0, 0.1);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .dark a.back-button {
          border: 1px solid rgba(255, 255, 255, 0.1);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      }
    </style>
    <link rel="icon" type="image/png" href="flashcard.png">
  </head>
  <body
    class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen font-sans flex items-center justify-center relative">
    <a
      href="/index.php"
      class="back-button absolute top-4 left-4 p-2 bg-white dark:bg-gray-700 shadow hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 text-gray-800 dark:text-white"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
    </a>

    <div
      class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-lg space-y-6">
      <h1 class="text-3xl font-bold mb-6 text-center">Cài đặt</h1>

      <div id="main-settings">
        <div class="space-y-4 mb-6">
          <div class="flex items-center justify-between">
            <span class="text-lg">Tên người dùng:</span>
            <span class="font-semibold text-blue-600 dark:text-blue-400">
              <?php echo htmlspecialchars($username); ?>
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-lg">Email:</span>
            <span class="font-semibold text-gray-700 dark:text-gray-300">
              <?php echo htmlspecialchars($email); ?>
            </span>
          </div>
        </div>

        <button
          id="toggle-profile-btn"
          class="w-full px-4 py-3 bg-gray-200 dark:bg-gray-700 rounded-lg text-lg font-bold flex justify-between items-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors mb-6">
          <span>Chỉnh sửa thông tin người dùng</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 transform transition-transform duration-300"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <div class="mb-6 flex items-center justify-between">
          <label for="theme-toggle" class="flex items-center cursor-pointer">
            <span class="text-lg mr-3">Chế độ tối:</span>
            <div class="relative">
              <input type="checkbox" id="theme-toggle" class="sr-only" />
              <div class="block bg-gray-600 w-14 h-8 rounded-full"></div>
              <div
                class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full"></div>
            </div>
          </label>
        </div>

        <div class="flex justify-center">
          <a
            href="/api/logout.php"
            class="px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
            Đăng xuất
          </a>
        </div>
      </div>
      
      <div id="profile-edit-section" class="hidden">
        <h2 class="text-2xl font-bold mb-6 text-center">Chỉnh sửa thông tin</h2>
        
        <div class="mb-4">
          <button
            class="collapsible-btn w-full px-4 py-3 bg-gray-200 dark:bg-gray-700 rounded-lg text-lg font-bold flex justify-between items-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            <span>Đổi tên người dùng</span>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 transform transition-transform duration-300"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div class="collapsible-content p-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
            <form method="POST" action="/api/update_profile.php" class="space-y-2">
              <input
                type="text"
                name="new_username"
                placeholder="Tên người dùng mới"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 focus:outline-none focus:ring focus:border-blue-300"
                required />
              <button
                type="submit"
                name="update_username"
                class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors">
                Cập nhật tên
              </button>
            </form>
          </div>
        </div>

        <div class="mb-4">
          <button
            class="collapsible-btn w-full px-4 py-3 bg-gray-200 dark:bg-gray-700 rounded-lg text-lg font-bold flex justify-between items-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            <span>Đổi email</span>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 transform transition-transform duration-300"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div class="collapsible-content p-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
            <form method="POST" action="update_profile.php" class="space-y-2">
              <input
                type="email"
                name="new_email"
                placeholder="Email mới"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 focus:outline-none focus:ring focus:border-blue-300"
                required />
              <button
                type="submit"
                name="update_email"
                class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors">
                Cập nhật email
              </button>
            </form>
          </div>
        </div>

        <div class="mb-4">
          <button
            class="collapsible-btn w-full px-4 py-3 bg-gray-200 dark:bg-gray-700 rounded-lg text-lg font-bold flex justify-between items-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            <span>Đổi mật khẩu</span>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 transform transition-transform duration-300"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div class="collapsible-content p-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
            <form method="POST" action="update_password.php" class="space-y-2">
              <input
                type="password"
                name="current_password"
                placeholder="Mật khẩu hiện tại"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 focus:outline-none focus:ring focus:border-blue-300"
                required />
              <input
                type="password"
                name="new_password"
                placeholder="Mật khẩu mới"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 focus:outline-none focus:ring focus:border-blue-300"
                required />
              <input
                type="password"
                name="confirm_password"
                placeholder="Xác nhận mật khẩu mới"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 focus:outline-none focus:ring focus:border-blue-300"
                required />
              <button
                type="submit"
                name="update_password"
                class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors">
                Cập nhật mật khẩu
              </button>
            </form>
          </div>
        </div>
        
        <div class="flex justify-center mt-6">
          <button
            id="back-to-settings-btn"
            class="px-6 py-3 bg-gray-400 text-white font-bold rounded-lg hover:bg-gray-500 transition-colors">
            Quay lại
          </button>
        </div>
      </div>
    </div>

    <script>
      // Khởi tạo Dark Mode
      const themeToggle = document.getElementById("theme-toggle");
      const html = document.documentElement;

      // Hàm cập nhật chế độ giao diện và lưu vào Local Storage
      function updateTheme(theme) {
        if (theme === "dark") {
          html.classList.add("dark");
          localStorage.setItem("theme", "dark");
          themeToggle.checked = true;
        } else {
          html.classList.remove("dark");
          localStorage.setItem("theme", "light");
          themeToggle.checked = false;
        }
      }

      // Kiểm tra chế độ đã lưu trong Local Storage khi tải trang
      const currentTheme = localStorage.getItem("theme");
      if (currentTheme) {
        updateTheme(currentTheme);
      } else {
        // Nếu chưa có, mặc định là chế độ sáng
        updateTheme("light");
      }

      // Lắng nghe sự kiện thay đổi trên nút chuyển đổi Dark Mode
      themeToggle.addEventListener("change", () => {
        if (themeToggle.checked) {
          updateTheme("dark");
        } else {
          updateTheme("light");
        }
      });
      
      // Xử lý chuyển đổi giữa hai phần giao diện (cài đặt chính và chỉnh sửa hồ sơ)
      const mainSettings = document.getElementById('main-settings');
      const profileEditSection = document.getElementById('profile-edit-section');
      const toggleProfileBtn = document.getElementById('toggle-profile-btn');
      const backToSettingsBtn = document.getElementById('back-to-settings-btn');
      
      // Khi nhấn nút "Chỉnh sửa thông tin người dùng"
      toggleProfileBtn.addEventListener('click', () => {
        mainSettings.classList.add('hidden'); // Ẩn phần cài đặt chính
        profileEditSection.classList.remove('hidden'); // Hiển thị phần chỉnh sửa
      });
      
      // Khi nhấn nút "Quay lại"
      backToSettingsBtn.addEventListener('click', () => {
        profileEditSection.classList.add('hidden'); // Ẩn phần chỉnh sửa
        mainSettings.classList.remove('hidden'); // Hiển thị lại phần cài đặt chính
      });

      // Xử lý các mục có thể thu gọn bên trong phần chỉnh sửa thông tin
      const collapsibleButtons = document.querySelectorAll('.collapsible-btn');
      collapsibleButtons.forEach(button => {
        button.addEventListener('click', () => {
          const content = button.nextElementSibling;
          const icon = button.querySelector('svg');
          
          // Đóng tất cả các mục khác trước khi mở mục hiện tại
          collapsibleButtons.forEach(otherButton => {
            if (otherButton !== button) {
              otherButton.nextElementSibling.classList.remove('expanded');
              otherButton.querySelector('svg').classList.remove('rotate-180');
            }
          });
          
          // Mở/đóng mục hiện tại bằng cách thêm/xóa lớp 'expanded' và 'rotate-180'
          content.classList.toggle('expanded');
          icon.classList.toggle('rotate-180');
        });
      });
    </script>
  </body>
</html>