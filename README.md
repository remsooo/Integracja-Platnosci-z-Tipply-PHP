# Integracja Płatności z Tipply (PHP)
Ten skrypt PHP służy do inicjowania i weryfikacji płatności (darowizn) za pośrednictwem platformy Tipply, wykorzystując zewnętrzny serwis `api.remus.wtf`. Kod dzieli się na dwie główne części: obsługę żądań POST do weryfikacji płatności (webhook) oraz generowanie i wysyłanie danych do zewnętrznego API w celu rozpoczęcia nowej transakcji.

Jasne, przygotowałem kompleksowy opis tego kodu PHP, który możesz wykorzystać na GitHubie. Obejmuje on cel skryptu, jego działanie, strukturę oraz szczegółowe wyjaśnienie każdej sekcji.

Integracja Płatności z Tipply (PHP)
Ten skrypt PHP służy do inicjowania i weryfikacji płatności (darowizn) za pośrednictwem platformy Tipply, wykorzystując zewnętrzny serwis api.remus.wtf. Kod dzieli się na dwie główne części: obsługę żądań POST do weryfikacji płatności (webhook) oraz generowanie i wysyłanie danych do zewnętrznego API w celu rozpoczęcia nowej transakcji.

## Spis Treści
- [Integracja Płatności z Tipply (PHP)](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#integracja-p%C5%82atno%C5%9Bci-z-tipply-php)
 - [Opis](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#opis)
 - [Wymagania](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#wymagania)
 - [Struktura Kodu](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#struktura-kodu)
   - [Obsługa Żądań POST (Webhook)](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#obs%C5%82uga-%C5%BC%C4%85da%C5%84-post-webhook)
   - [Generowanie Identyfikatora Zamówienia](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#generowanie-identyfikatora-zam%C3%B3wienia)
   - [Konfiguracja Danych Transakcji](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#konfiguracja-danych-transakcji)
   - [Wysyłanie Danych do API Zewnętrznego](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#wysy%C5%82anie-danych-do-api-zewn%C4%99trznego)
 - [Jak Używać](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#jak-u%C5%BCywa%C4%87)
   - [Konfiguracja](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#konfiguracja)
   - [Integracja](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#integracja)
 - [Kontakt](https://github.com/remsooo/Integracja-Platnosci-z-Tipply-PHP/blob/main/README.md#kontakt)

## Opis
Głównym celem tego skryptu jest zapewnienie mechanizmu do:
1. Inicjowania płatności Tipply: Zbieranie niezbędnych informacji o darowiźnie (nickname, email, kwota, wiadomość, cel, metoda płatności) i przesyłanie ich do zewnętrznego API.
2. Obsługi webhooków: Przyjmowanie powiadomień zwrotnych od platformy płatniczej (lub pośredniczącego API) po zakończeniu transakcji i weryfikowanie ich autentyczności za pomocą funkcji skrótu HMAC.

## Wymagania
- Środowisko serwerowe z PHP: Skrypt wymaga serwera webowego (np. Apache, Nginx) z zainstalowanym PHP (zalecana wersja 7.4 lub nowsza).
- Rozszerzenie cURL dla PHP: Moduł `php-curl` musi być aktywny, ponieważ skrypt używa cURL do komunikacji z zewnętrznym API.
- Dostęp do API Tipply oraz `api.remus.wtf`: Upewnij się, że masz prawidłowe linki webhooków i celów z Tipply, a także dostęp do wspomnianego API.

## Struktura Kodu
Skrypt jest podzielony na kilka logicznych sekcji:

### Obsługa Żądań POST (Webhook)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $numerzamowienia = $data['id'] ?? null;
        $meil = $data['email'] ?? null;
        $cokupil = $data['message'] ?? null;
        $kwota = $data['amount'] ?? null;
        $code = $data['code'] ?? null;

        $data = $numerzamowienia.$meil.$cokupil.$kwota;
        $secretKey = 'https://widgets.tipply.pl/TIP_ALERT/'; // Klucz tajny do weryfikacji HMAC URL webhook
        $generatedHMAC = hash_hmac('sha256', $data, $secretKey);

        if (hash_equals($code, $generatedHMAC)) {
            http_response_code(200);
            echo 'HMAC jest poprawny!';
            // Tutaj dodaj logikę biznesową po pomyślnej płatności, np. aktualizację bazy danych, przyznanie przedmiotu/usługi
        } else {
            http_response_code(400);
            echo 'HMAC jest niepoprawny!';
        }
    } else {
        http_response_code(400);
        echo "Błąd: Nieprawidłowy format JSON.";
    }
    exit;
}
```
Ta sekcja jest uruchamiana, gdy skrypt odbiera żądanie HTTP POST. Jest to typowe dla mechanizmów webhooków, gdzie zewnętrzna usługa (np. bramka płatności, Tipply) wysyła powiadomienia o statusie transakcji.
- `file_get_contents('php://input')`: Odczytuje surowe dane JSON wysłane w ciele żądania POST.
- `json_decode($jsonData, true)`: Dekoduje dane JSON do tablicy asocjacyjnej PHP.
- Weryfikacja HMAC: Kładzie nacisk na bezpieczeństwo, sprawdzając, czy otrzymane dane nie zostały zmodyfikowane.
 - Wartości `id`, `email`, `message`, `amount` i `code` są pobierane z otrzymanych danych JSON.
 - Generowany jest HMAC (Hash-based Message Authentication Code) z połączonych danych transakcji (`numerzamowienia`, `meil`, `cokupil`, `kwota`) i zdefiniowanego `$secretKey`
 - `hash_equals($code, $generatedHMAC)`: Bezpiecznie porównuje otrzymany `code` (HMAC z wiadomości webhooka) z wygenerowanym HMAC. Jeśli są identyczne, oznacza to, że dane są autentyczne i pochodzą z zaufanego źródła.
- Kody odpowiedzi HTTP: Skrypt wysyła odpowiednie kody statusu HTTP (`200 OK` dla poprawnego HMAC, `400 Bad Request` dla błędów HMAC lub JSON, `405 Method Not Allowed` dla innych typów żądań).

### Generowanie Identyfikatora Zamówienia
```php
function generateOrderId() {
    $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    return $randomString;
}
$orderid = generateOrderId();
```
Ta funkcja `generateOrderId()` tworzy unikalny, 10-znakowy identyfikator zamówienia. Wykorzystuje `uniqid()` do wygenerowania unikalnego ID opartego na czasie i `md5()` do jego zahaszowania, co zapewnia losowość i unikalność.

### Konfiguracja Danych Transakcji
```php
$nickname = $orderid; // Pseudonim gracza lub unikalny identyfikator zamówienia
$email = 'test@test.com'; // Adres e-mail klienta
$message = 'test'; // Opcjonalna wiadomość np. VIP
$amount = 100; // Kwota w groszach (100 groszy to 1 zł)
$cel = 'https://widgets.tipply.pl/TIPS_GOAL/...'; // Link do celu Tipply
$link = 'remsooo'; // Nazwa użytkownika Tipply
$webhook = 'https://widgets.tipply.pl/TIP_ALERT/...'; // Link do widgetu alertów Tipply
$accepted = 'http://localhost/submit_form.php'; // URL przekierowania po płatności
$method = 'psc'; // Metoda płatności (np. psc, paypal, cashbill_blik, cashbill)
```
W tej sekcji definiowane są zmienne konfiguracyjne dla nowej transakcji. Są one używane do budowania ładunku JSON wysyłanego do zewnętrznego API. Pamiętaj, aby zaktualizować te wartości przed użyciem w środowisku produkcyjnym.

### Wysyłanie Danych do API Zewnętrznego
```php
$postData = json_encode([
    'nickname' => $nickname,
    'email' => $email,
    'message' => $message,
    'amount' => $amount,
    'methodNumber' => $cel,
    'link' => $link,
    'webhook' => $webhook,
    'accepted' => $accepted,
    'method' => $method,
]);

$ch = curl_init('http://api.remus.wtf/submit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode == 201) {
    preg_match('/Location:\s*(.*)/i', $response, $matches);
    if (isset($matches[1])) {
        $location = trim($matches[1]);
        curl_close($ch);
        echo "<script>window.open('$location', '_blank');</script>"; // Otworzenie okna płatności
        exit();
    }
}

curl_close($ch);
echo "Nie udało się znaleźć nagłówka Location. Odpowiedź serwera: $response";
```
Ta część kodu jest wykonywana, gdy skrypt nie odbiera żądania POST (czyli jest wywoływany normalnie, np. przez przeglądarkę).
- `json_encode(...)`: Tworzy obiekt JSON z danych transakcji.
- Inicjalizacja cURL: Przygotowuje żądanie HTTP POST do zewnętrznego API (`http://api.remus.wtf/submit`).
 - `CURLOPT_RETURNTRANSFER`: Zwraca wynik transferu jako string zamiast bezpośredniego wyprowadzania.
 - `CURLOPT_POST`: Ustawia metodę żądania na POST.
 - `CURLOPT_HTTPHEADER`: Ustawia nagłówki HTTP, informując serwer, że wysyłane są dane JSON.
 - `CURLOPT_POSTFIELDS`: Dołącza zakodowany JSON jako ciało żądania.
 - `CURLOPT_HEADER`: Włącza zwracanie nagłówków odpowiedzi.
- Obsługa odpowiedzi:
 - Sprawdza kod odpowiedzi HTTP (`$httpCode`). Jeśli kod to `201 Created`, oznacza to, że zewnętrzny serwis pomyślnie przetworzył żądanie i w nagłówku `Location` powinien znajdować się URL do bramki płatności.
 - Skrypt wyodrębnia ten URL i używa JavaScriptu (`window.open`) do otwarcia nowej karty lub okna przeglądarki, przekierowując użytkownika do strony płatności.
 - Jeśli nagłówek `Location` nie zostanie znaleziony lub kod HTTP jest inny niż `201`, wyświetla się błąd wraz z pełną odpowiedzią serwera.

## Jak Używać
### Konfiguracja
1. Zaktualizuj dane Tipply: Zmień zmienne `$cel`, `$link`, `$webhook` na swoje rzeczywiste wartości z konta Tipply.
2. Adres URL `accepted`: Upewnij się, że `$accepted` wskazuje na prawidłowy adres URL, pod którym Twój system będzie przetwarzał informacje o zakończonej płatności.
3. Adres API `api.remus.wtf`: Upewnij się, że adres `http://api.remus.wtf/submit` jest poprawny i dostępny.

### Integracja
1. Umieść ten kod PHP na swoim serwerze webowym.
2. Upewnij się, że Twoja strona internetowa lub aplikacja wywołuje ten skrypt, aby rozpocząć proces płatności, przekazując ewentualnie zmienne, które chcesz dynamicznie ustawić (np. `email`, `message`, `amount`).

## Kontakt
- Discord: `remsooo`
