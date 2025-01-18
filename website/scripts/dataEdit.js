function toggleEditSection(button) {
    const parentDiv = button.closest('.staff-item, .item');
    
    if (parentDiv) {
        const editSection = parentDiv.querySelector('.edit-section');
    
        if (editSection) {
            if (editSection.style.display === 'none' || editSection.style.display === '') {
                editSection.style.display = 'block';
            } else {
                editSection.style.display = 'none';
            }
        }
    }
}