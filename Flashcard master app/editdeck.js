// editdeck.js

// Import các hàm xử lý dữ liệu từ các file khác
import { getDecks } from './decks.js';
import { getCards, addCard, deleteCard, editCard } from './cards.js';

/**
 * Hàm khởi tạo trang, chạy khi trang đã tải xong.
 * Xử lý việc lấy ID deck, tìm deck và gọi hàm render.
 */
async function initializeEditDeck() {
  const params = new URLSearchParams(window.location.search);
  const deckId = Number(params.get('id')); // Lấy ID của deck từ URL

  // Xác thực ID deck
  if (isNaN(deckId)) {
    alert('Deck ID is invalid.');
    window.location.href = 'index.php'; // Quay lại trang decks
    return;
  }

  // Lấy danh sách decks để tìm tên của deck hiện tại
  const decks = await getDecks();
  if (!Array.isArray(decks)) {
    alert('Failed to load decks. Please try again.');
    return;
  }
  
  // Tìm deck trong danh sách dựa trên ID
  const currentDeck = decks.find(deck => Number(deck.id) === deckId);

  if (!currentDeck) {
    alert('Deck not found.');
    window.location.href = 'index.php'; // Quay lại trang decks nếu không tìm thấy
    return;
  }

  // Hiển thị tên deck lên tiêu đề trang
  document.getElementById('deckTitle').textContent = currentDeck.name;

  // Gọi hàm để render danh sách thẻ của deck này
  renderCardList(deckId);

  // Gắn sự kiện click cho nút "Studying"
  const learnBtn = document.getElementById('learnBtn');
  if (learnBtn) {
    learnBtn.addEventListener('click', function() {
      // Chuyển hướng đến trang học flashcard
      window.location.href = `/study.php?id=${deckId}`;
    });
  }
}

// Lấy phần tử chứa danh sách thẻ
const cardList = document.getElementById('cardList');
let cardsData = []; // Biến toàn cục để lưu trữ dữ liệu thẻ, dùng cho việc chỉnh sửa

/**
 * Lấy và hiển thị danh sách các thẻ bài.
 * @param {number} deckId ID của bộ thẻ.
 */
async function renderCardList(deckId) {
  // Lấy dữ liệu thẻ từ API và lưu vào biến toàn cục
  cardsData = await getCards(deckId);
  cardList.innerHTML = ''; // Xóa nội dung cũ

  if (!Array.isArray(cardsData) || cardsData.length === 0) {
    cardList.innerHTML = '<p class="text-gray-600">No cards yet. Add one!</p>';
    return;
  }

  // Duyệt qua từng thẻ và tạo phần tử HTML để hiển thị
  cardsData.forEach(card => {
    const cardEl = document.createElement('div');
    cardEl.className = 'bg-white rounded shadow p-4';
    
    let imageHtml = '';
    if (card.Image) {
        imageHtml = `<img src="${card.Image}" alt="Card Image" class="w-full h-48 object-cover rounded-lg mb-2">`;
    }

    cardEl.innerHTML = `
      ${imageHtml}
      <p class="font-semibold text-gray-800">Front: ${card.Front_text}</p>
      <p class="text-gray-600 mb-2">Back: ${card.Back_text}</p>
      <div class="flex justify-end space-x-4">
        <button class="text-blue-600 hover:underline edit-btn" data-id="${card.CardID}">Edit</button>
        <button class="text-red-600 hover:underline delete-btn" data-id="${card.CardID}">Delete</button>
      </div>
    `;
    cardList.appendChild(cardEl);
  });

  // Sau khi các thẻ đã được tạo, gắn sự kiện cho các nút của chúng
  attachCardEvents(deckId);
}

/**
 * Gắn các trình lắng nghe sự kiện cho các nút "Edit" và "Delete".
 * @param {number} deckId ID của bộ thẻ.
 */
function attachCardEvents(deckId) {
    // Gắn sự kiện cho các nút XÓA
    const deleteBtns = document.querySelectorAll('.delete-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const cardId = btn.dataset.id;
            
            const isConfirmed = window.confirm("Bạn có chắc chắn muốn xóa thẻ này không?");
            
            if (isConfirmed) {
                try {
                    // Gọi hàm xóa thẻ từ cards.js
                    await deleteCard(cardId);
                    // Cập nhật lại danh sách thẻ sau khi xóa thành công
                    renderCardList(deckId);
                } catch (error) {
                    console.error("Lỗi khi xóa thẻ:", error);
                    alert("Không thể xóa thẻ: " + error.message);
                }
            }
        });
    });

    // Gắn sự kiện cho các nút CHỈNH SỬA
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const cardId = btn.dataset.id;
            // Tìm thẻ bài tương ứng trong mảng dữ liệu đã lưu
            const card = cardsData.find(c => Number(c.CardID) === Number(cardId));
            
            if (card) {
                // Điền dữ liệu thẻ hiện tại vào các trường trong modal
                document.getElementById('cardIdInput').value = card.CardID;
                document.getElementById('frontInput').value = card.Front_text;
                document.getElementById('backInput').value = card.Back_text;

                // Xử lý hiển thị ảnh hiện có
                const imagePreview = document.getElementById('imagePreview');
                if (card.Image) {
                    imagePreview.src = card.Image;
                    imagePreview.classList.remove('hidden');
                } else {
                    imagePreview.src = '';
                    imagePreview.classList.add('hidden');
                }
                
                // Reset giá trị của input file
                document.getElementById('imageInput').value = ''; 
                
                // Hiển thị modal
                modal.classList.remove('hidden');
            }
        });
    });
}

// Lấy các phần tử của modal
const modal = document.getElementById('cardModal');
const openBtn = document.getElementById('addCardBtn');
const saveBtn = document.getElementById('saveCardBtn');
const cancelBtn = document.getElementById('cancelCardBtn');
const frontInput = document.getElementById('frontInput');
const backInput = document.getElementById('backInput');
const imageInput = document.getElementById('imageInput');

// Lắng nghe sự kiện click trên nút "Add Card" để mở modal
openBtn.addEventListener('click', () => {
  // Xóa tất cả các giá trị cũ để chuẩn bị cho việc thêm thẻ mới
  document.getElementById('cardIdInput').value = '';
  frontInput.value = '';
  backInput.value = '';
  imageInput.value = '';
  document.getElementById('imagePreview').src = '';
  document.getElementById('imagePreview').classList.add('hidden');
  // Hiển thị modal
  modal.classList.remove('hidden');
});

// Lắng nghe sự kiện click trên nút "Cancel" để đóng modal
cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

// Lắng nghe sự kiện click trên nút "Save" để lưu hoặc cập nhật thẻ
saveBtn.addEventListener('click', async () => {
  const params = new URLSearchParams(window.location.search);
  const deckId = Number(params.get('id'));

  const cardId = document.getElementById('cardIdInput').value;
  const front = frontInput.value.trim();
  const back = backInput.value.trim();
  
  // Xác thực dữ liệu đầu vào
  if (!front || !back) {
    alert('Front and Back fields are required.');
    return;
  }

  // Tạo đối tượng FormData để gửi dữ liệu, bao gồm cả file ảnh
  const formData = new FormData();
  formData.append('front', front);
  formData.append('back', back);
  formData.append('deckId', deckId); // Thêm deckId vào formData cho cả add và edit

  // Kiểm tra và thêm file ảnh nếu có
  if (imageInput.files.length > 0) {
      formData.append('image', imageInput.files[0]);
  }

  if (cardId) {
    // Nếu có cardId, đây là thao tác chỉnh sửa
    formData.append('cardId', Number(cardId));
    await editCard(formData);
  } else {
    // Nếu không có cardId, đây là thao tác thêm mới
    await addCard(formData);
  }

  // Sau khi thao tác thành công, cập nhật lại danh sách thẻ
  renderCardList(deckId);
  // Ẩn modal
  modal.classList.add('hidden');
});

// Gắn sự kiện để chạy hàm khởi tạo khi DOM đã được tải hoàn toàn
document.addEventListener('DOMContentLoaded', initializeEditDeck);