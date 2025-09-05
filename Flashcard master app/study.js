// study.js

// Chỉ import các hàm cần thiết cho logic SM-2
import { getCards, getDeckDetails, updateCardProgress } from './cards.js';

// Khai báo các biến toàn cục để lưu trữ trạng thái của phiên học
let cards = []; // Mảng chứa dữ liệu của tất cả các thẻ
let currentCardIndex = 0; // Chỉ số của thẻ hiện tại trong mảng
let cardsReviewedCount = 0; // Số thẻ đã được ôn tập trong phiên này
let isFlipped = false; // Trạng thái lật của thẻ (mặt trước/mặt sau)

// Gắn trình lắng nghe sự kiện khi DOM đã được tải hoàn toàn
document.addEventListener('DOMContentLoaded', async () => {
    // Lấy tham số ID từ URL
    const params = new URLSearchParams(window.location.search);
    const deckId = Number(params.get('id'));

    // Kiểm tra tính hợp lệ của Deck ID
    if (isNaN(deckId)) {
        // Nếu ID không hợp lệ, hiển thị modal thông báo lỗi
        const alertModal = document.createElement('div');
        alertModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center';
        alertModal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-xl text-center">
            <p class="text-lg font-semibold mb-4">Deck ID is invalid.</p>
            <button onclick="window.location.href='index.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            OK
            </button>
        </div>
        `;
        document.body.appendChild(alertModal);
        return;
    }

    // Lấy tên deck và cập nhật tiêu đề
    const deckDetails = await getDeckDetails(deckId);
    if (deckDetails) {
        document.getElementById('deckName').textContent = deckDetails.DeckName;
    }

    // Bắt đầu tải các thẻ bài
    loadCards(deckId);
});

/**
 * Lấy các thẻ bài từ API và khởi tạo phiên học.
 * @param {number} deckId ID của bộ thẻ.
 */
async function loadCards(deckId) {
    // Lấy thẻ từ API đã được sắp xếp và lọc
    cards = await getCards(deckId);
    
    // Kiểm tra nếu có thẻ nào để ôn tập không
    if (cards && cards.length > 0) {
        // Khởi tạo các thuộc tính SM-2 nếu chúng chưa có
        cards = cards.map(card => ({
            ...card,
            repetitions: Number(card.repetitions) || 0,
            easeFactor: Number(card.easeFactor) || 2.5,
            intervals: Number(card.intervals) || 1,
        }));
        currentCardIndex = 0;
        cardsReviewedCount = 0;
        displayCard(); // Hiển thị thẻ đầu tiên
    } else {
        // Nếu không có thẻ nào cần ôn tập hôm nay
        document.getElementById('flashcard').innerHTML = '<div class="card-content"><p>You have no cards to review today!</p></div>';
        // Ẩn tất cả các nút và chỉ số không cần thiết
        document.getElementById('remember-level-buttons').classList.add('hidden');
        document.getElementById('action-buttons').classList.add('hidden');
        document.getElementById('navigation-buttons').classList.add('hidden');
        // document.getElementById('stats-container').classList.add('hidden');
        document.getElementById('card-counter').classList.add('hidden');
    }
}

/**
 * Hiển thị thẻ hiện tại lên giao diện người dùng.
 */
function displayCard() {
    const card = cards[currentCardIndex];
    if (!card) {
        return;
    }
    
    // Lấy các phần tử DOM cho nội dung văn bản
    const cardFrontTextElement = document.getElementById('card-front');
    const cardBackTextElement = document.getElementById('card-back');

    // Lấy các phần tử DOM cho vùng chứa hình ảnh
    const cardFrontImageContainer = document.getElementById('card-front-image-container');
    const cardBackImageContainer = document.getElementById('card-back-image-container');

    // Xóa hình ảnh cũ trước khi thêm hình ảnh mới
    if (cardFrontImageContainer) cardFrontImageContainer.innerHTML = '';
    if (cardBackImageContainer) cardBackImageContainer.innerHTML = '';

    // Cập nhật nội dung văn bản
    cardFrontTextElement.textContent = card.Front_text;
    cardBackTextElement.textContent = card.Back_text;

    // Hiển thị hình ảnh nếu có
    if (card.Image) {
        const imgElement = document.createElement('img');
        imgElement.src = card.Image;
        imgElement.alt = 'Card Image';

        // Gắn hình ảnh vào cả mặt trước và mặt sau
        if (cardFrontImageContainer) {
            cardFrontImageContainer.appendChild(imgElement.cloneNode(true));
        }
        if (cardBackImageContainer) {
            cardBackImageContainer.appendChild(imgElement);
        }
    }

    // Cập nhật các chỉ số thống kê trên giao diện
    document.getElementById('repetition-count').textContent = card.repetitions;
    // Sử dụng optional chaining để xử lý trường hợp nextReview có thể null
    document.getElementById('next-review').textContent = card.nextReview ? new Date(card.nextReview).toLocaleDateString('vi-VN') : '-';
    document.getElementById('ef-factor').textContent = card.easeFactor.toFixed(2);
    document.getElementById('card-counter').textContent = `${currentCardIndex + 1}/${cards.length}`;
    
    // Reset trạng thái thẻ về mặt trước và ẩn các nút liên quan
    document.getElementById('flashcard').classList.remove('flipped');
    document.getElementById('card-back-content').classList.add('hidden');
    document.getElementById('remember-level-buttons').classList.add('hidden');
    document.getElementById('action-buttons').classList.remove('hidden');
    isFlipped = false;
    
    // Kiểm tra và vô hiệu hóa các nút điều hướng
    document.getElementById('prev-btn').disabled = currentCardIndex === 0;
    // Nút "next" luôn được kích hoạt để có thể chuyển đến thẻ tiếp theo sau khi đánh giá
    document.getElementById('next-btn').disabled = false;
}

/**
 * Xử lý hiệu ứng lật thẻ và hiển thị/ẩn các nút điều khiển.
 */
function flipCard() {
    const flashcard = document.getElementById('flashcard');
    const cardBackContent = document.getElementById('card-back-content');
    const rememberLevelButtons = document.getElementById('remember-level-buttons');
    const actionButtons = document.getElementById('action-buttons');

    if (isFlipped) { // Nếu đang ở mặt sau, lật lại mặt trước
        flashcard.classList.remove('flipped');
        cardBackContent.classList.add('hidden');
        rememberLevelButtons.classList.add('hidden'); // Ẩn nút đánh giá
        actionButtons.classList.remove('hidden'); // Hiển thị nút "Lật thẻ"
    } else { // Nếu đang ở mặt trước, lật sang mặt sau
        flashcard.classList.add('flipped');
        cardBackContent.classList.remove('hidden');
        rememberLevelButtons.classList.remove('hidden'); // Hiển thị nút đánh giá
        actionButtons.classList.add('hidden'); // Ẩn nút "Lật thẻ"
    }
    isFlipped = !isFlipped;
}
document.getElementById('flip-btn').addEventListener('click', flipCard);

// Gắn sự kiện cho nút "Quay về"
document.getElementById('back-btn').addEventListener('click', () => {
    window.location.href = 'index.php';
});

// Thêm sự kiện cho các nút đánh giá SM-2
document.getElementById('remember-level-buttons').addEventListener('click', async (event) => {
    const button = event.target.closest('button');
    if (button) {
        const currentCard = cards[currentCardIndex];
        const quality = Number(button.dataset.level); // Lấy giá trị chất lượng (1-4) từ nút

        if (currentCard) {
            // Cập nhật thuộc tính của thẻ theo thuật toán SM-2
            updateCardProperties(currentCard, quality);

            // Gửi dữ liệu đã cập nhật lên server
            await updateCardProgress({ 
                CardID: currentCard.CardID,
                repetitions: currentCard.repetitions,
                easeFactor: currentCard.easeFactor,
                intervals: currentCard.intervals,
                nextReview: new Date(currentCard.nextReview).toISOString().split('T')[0] // Đảm bảo định dạng YYYY-MM-DD
            });
            
            cardsReviewedCount++;

            // Tự động chuyển đến thẻ tiếp theo
            moveToNextCard();
        }
    }
});

/**
 * Cập nhật thuộc tính của thẻ theo thuật toán SM-2.
 * @param {object} card Đối tượng thẻ cần cập nhật.
 * @param {number} quality Mức độ ghi nhớ (1: again, 2: hard, 3: good, 4: easy).
 */
function updateCardProperties(card, quality) {
    let { repetitions, easeFactor, intervals } = card; 

    // Ánh xạ mức độ ghi nhớ sang giá trị chất lượng (0-5)
    const qualityMap = { 1: 0, 2: 3, 3: 4, 4: 5 };
    const q = qualityMap[quality];

    // Tính toán Ease Factor mới
    let newEF = easeFactor + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02));
    newEF = Math.max(1.3, newEF); // Đảm bảo EF không nhỏ hơn 1.3
    newEF = Math.min(2.5, newEF); // Giới hạn EF không quá 2.5

    // Cập nhật số lần lặp lại và khoảng thời gian
    if (q < 3) {
        repetitions = 0; // Nếu quên, reset lại số lần lặp lại về 0
        intervals = 1;
    } else {
        repetitions += 1;
        if (repetitions === 1) intervals = 1; // Lần lặp đầu tiên, khoảng 1 ngày
        else if (repetitions === 2) intervals = 6; // Lần lặp thứ hai, khoảng 6 ngày
        else intervals = Math.ceil(intervals * newEF); // Lần lặp tiếp theo, tính theo công thức SM-2
    }

    // Cập nhật các thuộc tính của thẻ
    card.repetitions = repetitions;
    card.easeFactor = newEF;
    card.intervals = intervals;
    
    // Tính ngày ôn tập tiếp theo
    const nextReviewDate = new Date();
    nextReviewDate.setDate(nextReviewDate.getDate() + intervals);
    card.nextReview = nextReviewDate;
}

// Hàm điều hướng đến thẻ tiếp theo
function moveToNextCard() {
    if (currentCardIndex < cards.length - 1) {
        currentCardIndex++;
        displayCard();
    } else {
        endSession(); // Nếu là thẻ cuối cùng, kết thúc phiên
    }
}

// Hàm điều hướng đến thẻ trước đó
function moveToPreviousCard() {
    if (currentCardIndex > 0) {
        currentCardIndex--;
        displayCard();
    }
}

// Gắn sự kiện cho các nút điều hướng
document.getElementById('prev-btn').addEventListener('click', moveToPreviousCard);
document.getElementById('next-btn').addEventListener('click', moveToNextCard);

/**
 * Hiển thị thông báo khi phiên học kết thúc.
 */
function endSession() {
    // Thay thế nội dung của flashcard bằng thông báo kết thúc
    document.getElementById('flashcard').innerHTML = `
    <div class="card-content text-center">
        <h2 class="text-2xl font-bold mb-4">Bạn đã hoàn thành buổi học!</h2>
        <p class="text-lg mb-6">Bạn đã ôn tập ${cardsReviewedCount} thẻ hôm nay.</p>
        <button id="back-btn-end" class="action-btn btn-new">Quay về</button>
    </div>
    `;
    // Gắn sự kiện cho nút "Quay về" mới tạo
    document.getElementById('back-btn-end').addEventListener('click', () => {
        window.location.href = 'index.php';
    });
    
    // Ẩn tất cả các nút và chỉ số không còn cần thiết
    const rememberLevelButtons = document.getElementById('remember-level-buttons');
    const actionButtons = document.getElementById('action-buttons');
    const navigationButtons = document.getElementById('navigation-buttons');
    const statsContainer = document.querySelector('.stats-container');
    const cardCounter = document.getElementById('card-counter');

    if (rememberLevelButtons) rememberLevelButtons.classList.add('hidden');
    if (actionButtons) actionButtons.classList.add('hidden');
    if (navigationButtons) navigationButtons.classList.add('hidden');
    if (statsContainer) statsContainer.classList.add('hidden');
    if (cardCounter) cardCounter.classList.add('hidden');
}