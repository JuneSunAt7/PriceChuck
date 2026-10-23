
document.addEventListener('DOMContentLoaded', function() {
    console.log('Price Tracker загружен');
    

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Удалить этот товар?')) {
                e.preventDefault();
            }
        });
    });
});