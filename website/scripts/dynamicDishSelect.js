function fetchMenu(type) {
    document.getElementById('menu-items').classList.remove('hidden');
    document.getElementById('menu-list').innerHTML = '';

    fetch(`./controller/fetchMenu.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                let div = document.createElement('div');
                div.classList.add('menu-item');
                
                if (type === 'dishes') {
                    div.setAttribute('data-id', item.dish_id); // ID dania
                    div.setAttribute('data-name', item.dish_name); // Nazwa dania
                    div.setAttribute('data-price', item.price); // Cena
                    div.innerHTML = `<strong>${item.dish_name}</strong> - ${item.price} zł`;
                } else if (type === 'additions') {
                    div.setAttribute('data-id', item.addition_id); // ID dodatku
                    div.setAttribute('data-name', item.addition_name); // Nazwa dodatku
                    div.setAttribute('data-price', item.price); // Cena
                    div.innerHTML = `<strong>${item.addition_name}</strong> - ${item.price} zł`;
                }

                div.onclick = () => selectItem(div, type);
                document.getElementById('menu-list').appendChild(div);
            });
        })
        .catch(error => console.log('Error fetching menu:', error));
}

function selectItem(element, type) {
    let id = element.getAttribute('data-id');
    let name = element.getAttribute('data-name');
    let price = element.getAttribute('data-price');

    let inputId = (type === 'dishes') ? 'dishes' : 'additions';
    let items = JSON.parse(document.getElementById(inputId).value);

    let itemIndex = items.findIndex(item => item.id === id);

    if (itemIndex === -1) {
        items.push({ id, name, price, quantity: 1 });
    } else {
        items[itemIndex].quantity += 1;
    }

    document.getElementById(inputId).value = JSON.stringify(items);
    updateOrderSummary(type, items);
}

function updateOrderSummary(type, items) {
    let summarySection = document.getElementById(`${type}-summary`);

    if (!summarySection) {
        console.log(`Brak sekcji podsumowania dla ${type}`);
        return;
    }

    summarySection.innerHTML = `<h4>Wybrane ${type === 'dishes' ? 'dania' : 'dodatki'}</h4>`;

    items.forEach(item => {
        let div = document.createElement('div');
        div.classList.add('order-item');

        // Tworzymy element HTML z przyciskiem do usunięcia
        div.innerHTML = `
            <p><strong>${item.name}</strong> - ${item.price} zł x ${item.quantity}</p>
            <button class="remove-item" onclick="removeItem('${item.id}', '${type}')">Usuń</button>
        `;

        summarySection.appendChild(div);
    });
}

function removeItem(id, type) {
    let inputId = (type === 'dishes') ? 'dishes' : 'additions';
    let items = JSON.parse(document.getElementById(inputId).value);

    let itemIndex = items.findIndex(item => item.id === id);

    if (itemIndex !== -1) {
        items[itemIndex].quantity -= 1;

        if (items[itemIndex].quantity <= 0) {
            items.splice(itemIndex, 1);
        }
    }

    document.getElementById(inputId).value = JSON.stringify(items);
    updateOrderSummary(type, items);
}

function closeMenu() {
    document.getElementById('menu-items').classList.add('hidden');
}