class AdminPanel {
    constructor() {
        this.initFilters();
        this.initNotifications();
    }
    
    initFilters() {
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => this.applyFilters());
        });
    }
    
    applyFilters() {
        // Собирая значения фильтров
        const filters = {};
        document.querySelectorAll('.filter-select').forEach(select => {
            if (select.value) {
                filters[select.name] = select.value;
            }
        });
        
        // Перенаправляя с фильтрами
        const url = new URL(window.location.href);
        Object.keys(filters).forEach(key => {
            url.searchParams.set(key, filters[key]);
        });
        window.location.href = url.toString();
    }
    
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    changeStatus(bookingId, status) {
        fetch('ajax/update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: bookingId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Статус заявки изменен');
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
}

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    window.admin = new AdminPanel();
});