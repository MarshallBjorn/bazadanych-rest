# Przeniesienie: PostgreSQL w MongoDB

W tym pliku przedstawię kroki przeniesienia bazy danych w PostgreSQL w MongoDB.

## 1. Analiza schematów oraz mapowanie kolekcji
### Kluczowe obserwacje
- Tabele w PostgreSQL powinne być przeniesione jako kolekcje do MongoDB
- Relacje między tabelami powinny zostać zamienione jako "zagnieżdżone dokumenty" albo referencje.
### Wstępne mapowanie
1. Adresses
Kolekcja MongoDB: `address`.
Każdy adres stanie się dokumentem z odpowiednimi wartościami.
2. Providers
Kolekcja MongoDB: `provider`.
Z zagnieżdżonymi danymi `address` albo `address_id` jako referencja.
3. Staff
Kolekcja MongoDB: `staff`.
Z zagnieżdżonymi danymi `address` dla szybkiego dostępu.
4. Users(auth)
Kolekcja MongoDB: `user`.
Tutaj po prostu zamieszczamy spokojnie dane.
5. Components
Kolekcja MongoDB: `component`.
Z zagnieżdżonymi danymi `provider` albo `provider_id` jako referencja.
6. Dishes
Kolekcja MongoDB: `dish`.
Tutaj po prostu zamieszczamy spokojnie dane.
7. Dishes_Components
Kolekcja MongoDB: Scalamy z `dish`.
Zagnieżdżamy `component` i ich ilość `quantity` prosto w dokument `dish`. 
8. Additions
Kolekcja MongoDB: `addition`.
Tworzymy referencje na `provider`.
9. Dishes_Additions
Kolekcja MongoDB: Scalamy z `dish` albo `addition`.
10. Payment_Methods
Kolekcja MongoDB: `payment_method`.
Tutaj po prostu zamieszczamy spokojnie dane.
11. Order_Statuses
Kolekcja MongoDB: `order_status`.
Tutaj po prostu zamieszczamy spokojnie dane.
12. Deliverers
Kolekcja MongoDB: Scalić z `staff` albo pozostawić odseparowanym.
13. Orders
Kolekcja MongoDB: `order`.
Zagnieżdżamy `address`, `dishes` oraz `addition`.
14. Orders_Dishes and Orders_Additions
Kolekcja MongoDB: Scalamy z `order`. 

## 2. Design schematu MongoDB
### Przykładowo: Kolekcja `order`
```
{
  "_id": "order_123",
  "payment_method": "Credit Card",
  "deliverer": {
    "pesel": "12345678901",
    "firstname": "Oleksii",
    "lastname": "Nawrocki
  },
  "order_status": "PROCESSING",
  "ordered_at": "2025-01-01T10:00:00Z",
  "last_status_update": "2025-01-01T12:00:00Z",
  "client_contact": "9876543210",
  "address": {
    "street": "ul. Wesola",
    "locality": "Rzeszów",
    "post_code": "23-232",
    "building_num": "42"
  },
  "note": "Proszę nie dodawać rodzynek do sernika",
  "dishes": [
    {
      "dish_id": "dish_001",
      "name": "Pizza Margheritta",
      "quantity": 2,
      "price": 15.99
    }
  ],
  "additions": [
    {
      "addition_id": "add_001",
      "name": "Sos czosnkowy",
      "quantity": 1,
      "price": 2.50
    }
  ]
}
```
## 3. Migracja danych
### Wydobycie danych
- Ta baza danych przechowuje dane dla inicjalizacji w postaci plików CSV.
- W przypadku migracji bazy danych z danymi które nie są przechowywane na stale w plikach musimy wydostać te informacje z PostgreSQL:
```
COPY addresses TO '/dataset/data/placeholder.csv' DELIMITER ',' CSV HEADER;
```
### Transformacja danych
- Do tego posłuży nam Python albo Node.js żeby zamienić CSV na MongoDB-kompatybilny JSON. Do przykładu użyję Python:
```
import pandas as pd
import json

df = pd.read_csv('/dataset/data/placeholder.csv')

json_data = df.to_dict(orient='records')

with open('placeholder.json' 'w') as f:
	json.dump(json_data, f, indent=4)
```
Bardziej skomplikowany skrypt można znaleźć w "mongoDB/extractor.py" w którym się znajduję rozszerzenie tego programu na obsługę dokumentów zagnieżdżonych.
### Załadowanie danych
- W tym celu używamy funkcjonalność MongoDB:
```
mongoimport --uri "mongodb://localhost:27017" --authenticationDatabase admin -u root -p example --db mydb --collection dishes --file .\mongoDB\dishes.json --jsonArray
```
## 4. Obserwacje
### 1. Design schematów
- Zagnieżdżamy dane dla relacji typu jeden-do-jeden oraz jeden-do-wielu.
- Używamy referencji dla wiele-do-wielu albo często używanych danych.
### 2. Walidacja
- Używamy schematów MongoDB dla zasad walidacji oraz kontroli poprawności i spójności danych.
### 3. Przenoszenie danych
- Dla przeniesienia danych z PostgreSQL do MongoDB warto stworzyć w PG/PLSQL funkcje dla szybszego wyciąganie danych w postać CSV.
- Warto zmodyfikować powyższy skrypt w Python dla automatyzacji procesu migracji danych w odpowiedni format JSON.
### 4. Testing
- Ważne również sprawdzić poprawność i spójność przeniesionych danych, a poza tym odpowiednie działanie kwerend.
