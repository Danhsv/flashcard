// dashboard.js
// Lắng nghe sự kiện 'DOMContentLoaded' để đảm bảo DOM đã được tải hoàn toàn
document.addEventListener('DOMContentLoaded', () => {
    // Lấy các phần tử HTML cần thiết
    const searchInput = document.getElementById('search-input');
    const searchResultsContainer = document.getElementById('search-results-container');
    const API_BASE_URL = '/api/';

    /**
     * Hiển thị kết quả tìm kiếm lên giao diện.
     * @param {Array} results Mảng các đối tượng thẻ flashcard.
     */
    function displaySearchResults(results) {
        // Xóa nội dung cũ để chuẩn bị hiển thị kết quả mới
        searchResultsContainer.innerHTML = '';
        
        // Kiểm tra nếu không có kết quả nào
        if (results.length === 0) {
            // Hiển thị thông báo "Không tìm thấy thẻ nào"
            searchResultsContainer.innerHTML = `<p class="text-gray-500 dark:text-gray-400 text-center col-span-full">Không tìm thấy thẻ nào phù hợp.</p>`;
        } else {
            // Duyệt qua từng kết quả tìm được
            results.forEach(card => {
                // Tạo một phần tử div cho mỗi thẻ
                const cardElement = document.createElement('div');
                // Thêm các lớp CSS để tạo kiểu
                cardElement.className = 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-xl search-card';
                
                let imageHtml = '';
                // Nếu thẻ có ảnh, tạo thẻ img
                if (card.Image) {
                    imageHtml = `<img src="${card.Image}" alt="Hình ảnh thẻ" class="w-full h-auto object-cover rounded-lg mb-4">`;
                }

                // Chèn nội dung HTML của thẻ vào phần tử
                cardElement.innerHTML = `
                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-2">Deck: ${card.DeckName}</p>
                    ${imageHtml}
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">${card.Front_text}</h3>
                    <p class="text-gray-600 dark:text-gray-300">${card.Back_text}</p>
                `;
                // Gắn thẻ vào vùng chứa kết quả
                searchResultsContainer.appendChild(cardElement);
            });
        }
    }

    /**
     * Gửi yêu cầu tìm kiếm đến backend.
     * @param {string} query Từ khóa tìm kiếm.
     */
    async function searchFlashcards(query) {
        // Tối ưu: Nếu độ dài query < 2, không gửi yêu cầu
        if (query.length < 2) {
            searchResultsContainer.classList.add('hidden'); // Ẩn vùng kết quả
            return;
        }
        
        try {
            // Sử dụng Axios để gửi yêu cầu GET đến API
            const response = await axios.get(`${API_BASE_URL}search_flashcards.php?q=${encodeURIComponent(query)}`);
            // Hiển thị kết quả nhận được
            displaySearchResults(response.data);
            // Hiển thị vùng chứa kết quả
            searchResultsContainer.classList.remove('hidden');
        } catch (error) {
            // Bắt lỗi và hiển thị thông báo lỗi
            console.error('Lỗi khi tìm kiếm:', error);
            searchResultsContainer.innerHTML = `<p class="text-red-500 text-center col-span-full">Đã xảy ra lỗi khi tìm kiếm.</p>`;
            searchResultsContainer.classList.remove('hidden');
        }
    }

    // Lắng nghe sự kiện 'input' trên ô tìm kiếm (mỗi khi người dùng gõ)
    searchInput.addEventListener('input', () => {
        // Xóa bộ đếm thời gian cũ để tránh gửi quá nhiều yêu cầu (kỹ thuật debouncing)
        clearTimeout(window.searchTimeout);
        const query = searchInput.value.trim();
        // Đặt một bộ đếm thời gian mới
        window.searchTimeout = setTimeout(() => {
            searchFlashcards(query); // Gửi yêu cầu tìm kiếm sau 300ms
        }, 300);
    });
});