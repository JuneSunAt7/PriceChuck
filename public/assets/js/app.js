// Базовый JS для проекта
document.addEventListener('DOMContentLoaded', function() {
    console.log('Price Tracker загружен');
    
    // Подтверждение удаления
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Удалить этот товар?')) {
                e.preventDefault();
            }
        });
    });
});