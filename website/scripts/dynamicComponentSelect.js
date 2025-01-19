function fetchMenu2(type) {
    document.getElementById('menu-items-2').classList.remove('hidden');  // Używamy nowego ID
    document.getElementById('menu-list-2').innerHTML = '';  // Używamy nowego ID

    fetch(`./controller/fetchMenu2.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                let div = document.createElement('div');
                div.classList.add('menu-item');
                if (type === 'components') {
                    div.setAttribute('data-id', item.component_id);
                    div.setAttribute('data-name', item.component_name);
                    div.setAttribute('data-price', item.price);
                    div.innerHTML = `<strong>${item.component_name}</strong> - ${item.price} zł`;
                }

                div.onclick = () => selectItem2(div, type);
                document.getElementById('menu-list-2').appendChild(div);  // Używamy nowego ID
            });
        })
        .catch(error => console.log('Error fetching menu:', error));
}

function selectItem2(element, type) {
    let id = element.getAttribute('data-id');
    let name = element.getAttribute('data-name');
    let price = element.getAttribute('data-price');

    let inputId = (type === 'components') ? 'components' : '';
    let items = JSON.parse(document.getElementById(inputId).value);

    let itemIndex = items.findIndex(item => item.id === id);

    if (itemIndex === -1) {
        items.push({ id, name, price, quantity: 1 });
    } else {
        items[itemIndex].quantity += 1;
    }

    document.getElementById(inputId).value = JSON.stringify(items);
    updateOrderSummary2(type, items);
}

function updateOrderSummary2(type, items) {
    let summarySection = document.getElementById(`${type}-summary`);

    if (!summarySection) {
        console.log(`Brak sekcji podsumowania dla ${type}`);
        return;
    }

    summarySection.innerHTML = `<h4>Wybrane składniki</h4>`;

    items.forEach(item => {
        let div = document.createElement('div');
        div.classList.add('order-item');

        div.innerHTML = `
            <p><strong>${item.name}</strong> - ${item.price} zł x ${item.quantity}</p>
            <button class="remove-item" onclick="removeItem2('${item.id}', '${type}')">Usuń</button>
        `;

        summarySection.appendChild(div);
    });
}

function removeItem2(id, type) {
    let inputId = (type === 'components') ? 'components' : '';
    let items = JSON.parse(document.getElementById(inputId).value);

    let itemIndex = items.findIndex(item => item.id === id);

    if (itemIndex !== -1) {
        items[itemIndex].quantity -= 1;

        if (items[itemIndex].quantity <= 0) {
            items.splice(itemIndex, 1);
        }
    }

    document.getElementById(inputId).value = JSON.stringify(items);
    updateOrderSummary2(type, items);
}

function closeMenu2() {
    document.getElementById('menu-items-2').classList.add('hidden');
}