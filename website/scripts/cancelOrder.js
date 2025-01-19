function cancelOrder(orderId) {
    fetch('./controller/cancelOrderHandler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order_id: orderId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Zamówienie zostało anulowane.");
            location.reload();
        } else {
            alert("Wystąpił błąd: " + data.message);
        }
    })
    .catch(error => {
        console.error("Błąd:", error);
        alert("Wystąpił problem podczas anulowania zamówienia.");
    });
}