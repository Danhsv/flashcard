// ui.js
// Import các hàm cần thiết từ decks.js
// Mặc dù các hàm này không được dùng trực tiếp trong renderDeckList,
// chúng có thể được dùng ở các hàm khác trong file này
import { deleteDeck } from './decks.js';
import { getDecks } from './decks.js';

/**
 * Hiển thị danh sách các bộ thẻ lên giao diện.
 * Đây là hàm chính để render UI.
 * @param {Array} decks Mảng các đối tượng bộ thẻ được lấy từ backend.
 */
export function renderDeckList(decks) {
  // Lấy phần tử HTML có id="deckList"
  const container = document.getElementById('deckList');
  // Xóa nội dung HTML cũ để làm mới danh sách
  container.innerHTML = '';

  // Nếu không có bộ thẻ nào, hiển thị thông báo
  if (decks.length === 0) {
    container.innerHTML = '<p class="text-gray-600">No decks yet. Create one!</p>';
    return; // Dừng hàm
  }

  // Duyệt qua từng đối tượng deck trong mảng
  decks.forEach(deck => {
    // Tạo một phần tử div mới để làm "thẻ" flashcard
    const card = document.createElement('div');
    // Thêm các lớp CSS để tạo kiểu cho thẻ
    card.className = 'deck-card bg-white dark:bg-gray-800 rounded shadow p-4 hover:shadow-lg transition flex flex-col justify-between';

    // Sử dụng template string để chèn toàn bộ cấu trúc HTML vào thẻ
    card.innerHTML = `
      <div>
        <a href="editdeck.php?id=${deck.id}" class="block mb-2">
          <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-400">${deck.name}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-300">Tạo ngày: ${deck.date}</p>
        </a>
      </div>
      <div class="mt-3 space-y-2">
        <a href="study.php?id=${deck.id}" 
          class="inline-block w-full text-center px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
          ▶ Start Studying
        </a>
        <button 
          class="w-full text-sm px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition rename-btn"
          data-id="${deck.id}" data-name="${deck.name}">
          ✏️ Rename Deck
        </button>
        <button 
          class="w-full text-sm px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition delete-btn"
          data-id="${deck.id}">
          🗑️ Delete Deck
        </button>
      </div>
    `;
    // Gắn thẻ flashcard mới tạo vào vùng chứa
    container.appendChild(card);
  });
}

// Phần code dưới đây có vẻ là một phiên bản cũ hoặc lặp lại logic của main.js
// và có thể không hoạt động hiệu quả vì các nút được tạo động.

// Gắn các trình xử lý sự kiện cho các nút xóa và đổi tên
// document.querySelectorAll sẽ không tìm thấy các nút được tạo sau
const deleteButtons = document.querySelectorAll('.delete-btn');
deleteButtons.forEach(btn => {
  btn.addEventListener('click', (event) => {
    const deckId = event.target.dataset.id;
    const deckName = event.target.dataset.name;
    const deleteModal = document.getElementById('deleteModal');
    const deleteDeckName = document.getElementById('deleteDeckName');
    
    if (deleteModal && deleteDeckName) {
      deleteDeckName.textContent = deckName;
      deleteModal.dataset.deckId = deckId;
      deleteModal.classList.remove('hidden');
    }
  });
});

// Gắn trình xử lý sự kiện cho nút đổi tên
const renameButtons = document.querySelectorAll('.rename-btn');
renameButtons.forEach(btn => {
  btn.addEventListener('click', (event) => {
      const deckId = event.target.dataset.id;
      const deckName = event.target.dataset.name;
      // Gửi sự kiện tùy chỉnh để main.js có thể xử lý việc mở modal đổi tên
      const renameEvent = new CustomEvent('rename-deck', {
          detail: {
              id: deckId,
              name: deckName
          }
      });
      document.dispatchEvent(renameEvent);
  });
});
