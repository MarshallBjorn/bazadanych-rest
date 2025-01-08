function toggleEditSection(button) {
    const parentDiv = button.closest('.item');
    const editSection = parentDiv.querySelector('.edit-section');

    if (editSection.style.display === 'none' || editSection.style.display === '') {
        editSection.style.display = 'block';
    } else {
        editSection.style.display = 'none';
    }
}

function saveDish(dishId) {
    const parentDiv = document.getElementById(`dish-${dishId}`);
    const formData = new FormData(parentDiv.querySelector('form'));

    fetch('./controller/editDishHandler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert('Zmiany zostały zapisane!');
        location.reload();
    })
    .catch(error => {
        console.error('Wystąpił błąd:', error);
        alert('Nie udało się zapisać zmian.');
    });
}