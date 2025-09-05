

// Khai báo đường dẫn cơ sở đến API
const API_URL = '/api/';
// Import hàm renderDeckList từ ui.js để có thể gọi sau khi cập nhật dữ liệu
import { renderDeckList } from './ui.js';

// Hàm phụ trợ để định dạng lại ngày tháng từ server
function formatDeckDates(decks) {
    // Duyệt qua từng deck và định dạng lại trường 'date'
    return decks.map(deck => {
        if (deck.date && typeof deck.date === 'string') {
            // Thay thế khoảng trắng bằng 'T' để tạo định dạng ISO 8601 hợp lệ
            const formattedDateString = deck.date.replace(' ', 'T');
            // Tạo đối tượng Date từ chuỗi
            const dateObject = new Date(formattedDateString);
            // Định dạng lại ngày theo chuẩn tiếng Việt (dd/mm/yyyy)
            const formattedDate = dateObject.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            // Gán lại giá trị đã định dạng
            deck.date = formattedDate;
        }
        return deck; // Trả về đối tượng deck đã được xử lý
    });
}

// Hàm lấy tất cả các bộ thẻ từ database
// Export hàm này để các file khác (ví dụ: main.js) có thể sử dụng
export function getDecks() {
    // Gửi yêu cầu GET đến API
    return fetch(API_URL + 'gets_decks.php')
        .then(response => {
            // Kiểm tra xem phản hồi có thành công không (status code 200-299)
            if (!response.ok) {
                // Nếu không, ném ra lỗi
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            // Chuyển đổi phản hồi từ JSON thành đối tượng JavaScript
            return response.json();
        })
        .then(data => {
            // Sau khi nhận được dữ liệu, định dạng lại ngày tháng
            const decksWithFormattedDates = formatDeckDates(data);
            // Trả về dữ liệu đã được định dạng
            return decksWithFormattedDates;
        })
        .catch(error => {
            // Bắt và xử lý các lỗi
            console.error('Lỗi khi lấy danh sách bộ thẻ:', error);
            return []; // Trả về mảng rỗng nếu có lỗi
        });
}

// Hàm tạo một bộ thẻ mới trong database
export function createDeck(deckName) {
    const newDeck = { name: deckName };
    // Gửi yêu cầu POST đến API để tạo deck mới
    return fetch(API_URL + 'create_decks.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', // Báo cho server biết định dạng dữ liệu
        },
        body: JSON.stringify(newDeck), // Chuyển đối tượng thành chuỗi JSON để gửi đi
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Tạo bộ thẻ thất bại: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Tạo bộ thẻ thành công:', data);
        // Sau khi tạo thành công, gọi lại hàm getDecks để cập nhật giao diện
        getDecks().then(decks => renderDeckList(decks));
        return data;
    })
    .catch(error => {
        console.error('Lỗi khi tạo bộ thẻ:', error);
        return null;
    });
}

// Hàm xóa một bộ thẻ khỏi database
export function deleteDeck(deckId) {
    // Gửi yêu cầu POST để xóa deck theo ID
    return fetch(API_URL + 'delete_deck.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: deckId }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to delete deck: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Deck deleted successfully:', data);
        getDecks(); // Cập nhật lại danh sách decks sau khi xóa
        return data;
    })
    .catch(error => {
        console.error('Error deleting deck:', error);
        return null;
    });
}

// Hàm đổi tên một bộ thẻ
export function updateDeck(deckId, newName) {
    const updatedDeck = { id: deckId, name: newName };
    // Gửi yêu cầu POST để đổi tên deck
    return fetch(API_URL + 'rename_deck.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(updatedDeck),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Đổi tên bộ thẻ thất bại: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Đổi tên bộ thẻ thành công:', data);
        return data;
    })
    .catch(error => {
        console.error('Lỗi khi đổi tên bộ thẻ:', error);
        return null;
    });
}