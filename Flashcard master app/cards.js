// cards.js
// Khai báo đường dẫn cơ bản đến thư mục API
const API_BASE_URL = '/api/';

// Hàm bất đồng bộ để lấy danh sách thẻ bài của một bộ thẻ cụ thể
export async function getCards(deckId) {
    try {
        // Gửi yêu cầu GET đến API với deck_id trong URL
        const response = await fetch(`${API_BASE_URL}get_cards.php?deck_id=${deckId}`);
        // Chuyển đổi phản hồi JSON thành đối tượng JavaScript
        const data = await response.json();
        
        // Kiểm tra xem phản hồi có thành công không
        if (!response.ok) {
            // Nếu không, ném ra lỗi với thông điệp từ server hoặc thông điệp mặc định
            throw new Error(data.error || 'Failed to fetch cards.');
        }
        
        // Trả về dữ liệu thẻ bài
        return data;
    } catch (error) {
        // Xử lý và in lỗi ra console
        console.error('Error fetching cards:', error);
        // Hiển thị cảnh báo cho người dùng
        alert('Error fetching cards: ' + error.message);
        // Trả về mảng rỗng nếu có lỗi
        return [];
    }
}

// Hàm bất đồng bộ để thêm một thẻ bài mới
// Dữ liệu được truyền vào dưới dạng FormData
export async function addCard(formData) {
    try {
        // Gửi yêu cầu POST với FormData làm body
        const response = await fetch(`${API_BASE_URL}add_card.php`, {
            method: 'POST',
            // KHÔNG CẦN header 'Content-Type' vì FormData tự động thiết lập
            body: formData, // Trực tiếp sử dụng formData được truyền vào
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to add card.');
        }

        alert(data.message); // Thông báo thành công từ server
        return data;
    } catch (error) {
        console.error('Error adding card:', error);
        alert('Error adding card: ' + error.message);
    }
}

// Hàm bất đồng bộ để chỉnh sửa một thẻ bài
// Dữ liệu được truyền vào dưới dạng FormData
export async function editCard(formData) {
    try {
        // Gửi yêu cầu POST để chỉnh sửa thẻ bài
        const response = await fetch(`${API_BASE_URL}edit_card.php`, {
            method: 'POST',
            // KHÔNG CẦN header 'Content-Type'
            body: formData, // Sử dụng FormData
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to edit card.');
        }

        alert(data.message); // Thông báo thành công từ server
        return data;
    } catch (error) {
        console.error('Error editing card:', error);
        alert('Error editing card: ' + error.message);
    }
}

// Hàm bất đồng bộ để xóa một thẻ bài
export async function deleteCard(cardId) {
    try {
        // Gửi yêu cầu POST để xóa thẻ bài
        const response = await fetch(`${API_BASE_URL}delete_card.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // Gửi JSON body
            },
            body: JSON.stringify({ CardID: cardId }), // Gửi ID thẻ bài
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to delete card.');
        }

        alert(data.message);
        return data;
    } catch (error) {
        console.error('Error deleting card:', error);
        alert('Error deleting card: ' + error.message);
    }
}

// Hàm bất đồng bộ để lấy chi tiết của một bộ thẻ
export async function getDeckDetails(deckId) {
    try {
        // Gửi yêu cầu GET để lấy chi tiết deck
        const response = await fetch(`${API_BASE_URL}get_deck_details.php?deck_id=${deckId}`);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to fetch deck details.');
        }
        
        return data;
    } catch (error) {
        console.error('Error fetching deck details:', error);
        alert(`Error fetching deck details: ${error.message}`);
        return null;
    }
}

// Hàm bất đồng bộ để cập nhật tiến độ học của một thẻ bài (dùng cho SM-2)
export async function updateCardProgress(cardData) {
    try {
        // Gửi yêu cầu POST để cập nhật tiến độ
        const response = await fetch(`${API_BASE_URL}update_sm2_progress.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(cardData), // Gửi dữ liệu tiến độ
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to update progress.');
        }

        console.log(data.message);
        return data;
    } catch (error) {
        console.error('Error updating progress:', error);
        alert('Error updating progress: ' + error.message);
    }
}