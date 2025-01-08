function changeView(view) {
    document.getElementById("item-list").style.display = "none";
    document.getElementById("dish-add").style.display = "none";
    document.getElementById("order-list").style.display = "none";
    document.getElementById("employee-list").style.display = "none";
    document.getElementById("new-order").style.display = "none";

    switch (view) {
        case 'item-list':
            document.getElementById("item-list").style.display = "flex";
            break;
        case 'dish-add':
            document.getElementById("dish-add").style.display = "block";
            break;
        case 'order-list':
            document.getElementById("order-list").style.display = "flex";
            break;
        case 'employee-list':
            document.getElementById("employee-list").style.display = "flex";
            break;
        case 'new-order':
            document.getElementById("new-order").style.display = "block";
            break;
        default:
            console.warn(`Nieznany widok: ${view}`);
            break;
    }
}