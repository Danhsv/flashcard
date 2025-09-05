// Nhập các hàm xử lý dữ liệu từ file decks.js
import { getDecks, createDeck, deleteDeck, updateDeck } from './decks.js';
// Nhập hàm để render giao diện từ file ui.js
import { renderDeckList } from './ui.js';

// Đợi cho toàn bộ nội dung HTML được tải xong rồi mới chạy code JavaScript
document.addEventListener('DOMContentLoaded', () => {
  // Lấy danh sách deck từ backend khi trang được tải
  getDecks().then(decks => {
    // Sau khi nhận được dữ liệu, gọi hàm để hiển thị danh sách deck
    renderDeckList(decks);
  });

  // Tìm các phần tử HTML và lưu vào biến để dễ sử dụng
  const modal = document.getElementById('deckModal');
  const createBtn = document.getElementById('createDeckBtn');
  const saveBtn = document.getElementById('saveDeckBtn');
  const cancelBtn = document.getElementById('cancelDeckBtn');
  const deckListContainer = document.getElementById('deckList');

  // Thêm các biến và nút cho modal xóa
  const deleteModal = document.getElementById('deleteModal');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  let deckIdToDelete = null; // Biến để lưu ID của deck cần xóa

  // Thêm các biến và nút cho modal đổi tên
  const renameModal = document.getElementById('renameModal');
  const newDeckNameInput = document.getElementById('newDeckName');
  const renameDeckNameInput = document.getElementById('renameDeckName'); // Input mới cho modal đổi tên
  const saveRenameBtn = document.getElementById('saveRenameBtn');
  const cancelRenameBtn = document.getElementById('cancelRenameBtn');
  let deckIdToRename = null; // Biến để lưu ID của deck cần đổi tên

  // Lắng nghe sự kiện click cho nút "New Deck"
  createBtn.addEventListener('click', () => {
    newDeckNameInput.value = ''; // Xóa nội dung input cũ
    modal.classList.remove('hidden'); // Hiển thị modal tạo deck
  });
  // Lắng nghe sự kiện click cho nút "Cancel" trong modal tạo deck
  cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

  // Lắng nghe sự kiện click cho nút "Save" trong modal tạo deck
  saveBtn.addEventListener('click', () => {
    const name = newDeckNameInput.value.trim(); // Lấy tên deck từ input và xóa khoảng trắng thừa
    if (name) { // Nếu tên không trống
      createDeck(name).then(() => { // Gọi hàm tạo deck với tên mới
        // Sau khi tạo deck thành công, lấy lại danh sách mới
        getDecks().then(decks => {
          renderDeckList(decks); // Hiển thị lại danh sách deck đã được cập nhật
        });
      });
      modal.classList.add('hidden'); // Ẩn modal sau khi lưu
    }
  });

  // Sử dụng "event delegation" để lắng nghe sự kiện click trên toàn bộ container
  if (deckListContainer) {
    deckListContainer.addEventListener('click', (e) => {
      // Tìm phần tử cha gần nhất có class .delete-btn hoặc .rename-btn
      const deleteBtn = e.target.closest('.delete-btn');
      const renameBtn = e.target.closest('.rename-btn');

      if (deleteBtn) {
        // Nếu click vào nút xóa, lấy ID từ thuộc tính data-id và hiển thị modal xóa
        deckIdToDelete = deleteBtn.dataset.id;
        deleteModal.classList.remove('hidden');
      } else if (renameBtn) {
        // Nếu click vào nút đổi tên, lấy ID và tên hiện tại, hiển thị modal đổi tên
        deckIdToRename = renameBtn.dataset.id;
        const currentName = renameBtn.dataset.name;
        if (renameDeckNameInput) {
          renameDeckNameInput.value = currentName; // Đặt tên hiện tại vào input của modal
        }
        renameModal.classList.remove('hidden');
      }
    });
  }

  // Lắng nghe sự kiện cho các nút trong modal xóa
  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', () => {
      deleteModal.classList.add('hidden'); // Ẩn modal
      deckIdToDelete = null; // Xóa ID đã lưu
    });
  }

  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', () => {
      if (deckIdToDelete) { // Nếu có ID để xóa
        deleteDeck(parseInt(deckIdToDelete)).then(() => { // Gọi hàm xóa deck
          getDecks().then(decks => { // Lấy lại danh sách deck sau khi xóa
            renderDeckList(decks);
          });
        });
      }
      deleteModal.classList.add('hidden');
      deckIdToDelete = null;
    });
  }

  // Lắng nghe sự kiện cho các nút trong modal đổi tên
  if (cancelRenameBtn) {
    cancelRenameBtn.addEventListener('click', () => {
      renameModal.classList.add('hidden'); // Ẩn modal
      deckIdToRename = null; // Xóa ID đã lưu
    });
  }

  if (saveRenameBtn) {
    saveRenameBtn.addEventListener('click', () => {
      const newName = renameDeckNameInput.value.trim();
      if (deckIdToRename && newName) { // Nếu có ID và tên mới
        updateDeck(parseInt(deckIdToRename), newName).then(() => { // Gọi hàm cập nhật
          getDecks().then(decks => { // Lấy lại danh sách deck sau khi cập nhật
            renderDeckList(decks);
          });
        });
      }
      renameModal.classList.add('hidden');
      deckIdToRename = null;
    });
  }
});
