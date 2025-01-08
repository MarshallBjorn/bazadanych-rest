document.addEventListener("DOMContentLoaded", () => {
    const addStaffButton = document.getElementById("add-staff-button");
    const closeModalButton = document.getElementById("close-modal");
    const overlay = document.getElementById("overlay");
    const modal = document.getElementById("modal");

    // Otwórz modal
    addStaffButton.addEventListener("click", () => {
        overlay.classList.remove("hidden");
        modal.classList.remove("hidden");
    });

    // Zamknij modal
    closeModalButton.addEventListener("click", () => {
        overlay.classList.add("hidden");
        modal.classList.add("hidden");
    });

    // Zamknięcie modala po kliknięciu na overlay
    overlay.addEventListener("click", () => {
        overlay.classList.add("hidden");
        modal.classList.add("hidden");
    });
});