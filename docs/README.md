# Restaurant management project
## Dane do logowania
### pgAdmin:
**Adres:** ``localhost:8080``
**Login:** admin@example.com
**Hasło:** admin
### Baza danych:
**Login:** admin
**Hasło:** password
### Aplikacja internetowa:
**Adres:** ```localhost:8000```
**Login:** admin
**Hasło:** 1234
### MongoDB:
**Login:** ``admin``
**Hasło:** ``example``

## Instrukcja inicjalizacji i użytkowania.
### Instalacja Dockera
1. Należy pobrać Docker Desktop i zainstalować go. Oficjalna strona: [przejdź](https://www.docker.com/products/docker-desktop/).
2. Po instalacji należy upewnić się, że Docker jest aktywny. Łatwo to sprawdzić na dashbordzie aplikacji.
### Inicjalizacja bazy danych
1. Należy sclonować repozytorium w dowolne miejsce na komputerze.
2. W terminalu przejść do katalogu ```bazadanych-rest```.
3. Po upewnieniu się w tym, że Docker Desktop jest uruchomiony, użyć jedną z tych opcji:
	+ ```.\init.cmd``` *albo* ```./init.bash```(w zależności od systemu operacyjnego).
	+ ```.\start.cmd``` *albo* ```./start.bash```.
	+ Różnica pomiędzy tymi opcjami: [^1] 
4. Po wyczekaniu aż wszystkie kontenery zostaną uruchomione adresy ``localhost:8080`` oraz ``localhost:8000`` zostaną zajęte i odpowienio: pierwszy będzie system pgAdmin, a drugi to będzie interfejs zarządzania.
### Konfiguracja pgAdmin
Ta część insturkcji dotyczy tylko w sytuacji gdy:
* Po raz pierwszy wchodzimy do pgAdmina po sklonowaniu repozytorium.
* Przy użyciu polecenia Dockera ``docker compose down``[^2].

W przypadku jednej z tych dwóch opcji należy wykonać następujące kroki:
1. W wkładce ``Servers`` przejść **Register** > **Server**.
2. W nowootworzonym oknie pozostajemy w **General** i ustawiamy dowolną nazwę serwera.
3. Przechodzimy do **Connection** i tutaj należy ustawić następujące wartości:
	* **Host name/address:** ``postgres``.
	* **Port:** ``5432``.
	* **Username:** ``admin``.
	* **Password:** ``password``.
	* (*opcjonalnie*) **Save password?:** ``check``
4. Po wykonaniu powyższych kroków baza danych z danymi i funkcjonalnością zostanie załadowana i będzie możliwość wykonywania kwerend itd.

W przypadku gdy mamy skonfigurowany pgAdmin to nie ma potrzeby dokonywać żadnych zmian, jedynie logować się(w zależności od ustawienia "**Save password?**").
### Interfejs zarządzania
Jak nie pojawi się błędów w bazie danych, po zalogowaniu do systemu dostępna będzie cała funkcjonalność baza danych.

### Zatrzymanie aplikacji
W celu zatrzymania i wyłączenia aplikacji wystarczy w sesji terminalu(która była użyta do uruchomienia wcześniej) wcisnąć kombinację ``Ctrl + C``, albo alternatywnie ``docker compose down``[^2].

### Konfiguracja MongoDB

[^1]: ```init.*``` to jest skrypt w powłoce shell/cmd który usuwa (jeśli przed tym była inicjalizowana) bazę danych i tworzy ją ponownie na podstawie plików w katalogu ```dataset```. Natomiast ```start.*``` poprostu uruchamia stworzone wcześniej kontenery.
[^2]: ``docker compose down`` usuwa wszystkie stworzone kontenery wraz z tym: pgAdmina. Skrypty ``init.*`` usuwają jedynie kontener PostgreSQL, a pozostałe zostają w wcześniej ustalonej konfiguracji.

