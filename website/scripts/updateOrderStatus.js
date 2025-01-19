function updateOrderStatus(orderId) { {
        fetch('./controller/editOrderStatusHandler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Status zamówienia został zaktualizowany.");
                location.reload(); // Odśwież stronę, aby zobaczyć zmiany
            } else {
                alert("Wystąpił błąd: " + data.message);
            }
        })
        .catch(error => {
            console.error("Błąd:", error);
            alert("Wystąpił problem podczas aktualizacji statusu zamówienia.");
        });
    }
}