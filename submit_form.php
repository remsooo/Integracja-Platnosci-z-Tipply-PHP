<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (json_last_error() === JSON_ERROR_NONE) {

        $numerzamowienia = $data['id'] ?? null;
        $meil = $data['email'] ?? null;
        $cokupil = $data['message'] ?? null;
        $kwota = $data['amount'] ?? null;
        $code = $data['code'] ?? null;
        $method = $data['method'] ?? null;

        $data = $numerzamowienia.$meil.$cokupil.$kwota;
        $secretKey = 'https://widgets.tipply.pl/TIP_ALERT/';        // URL widgetu alertów o napiwkach Tipply
        $generatedHMAC = hash_hmac('sha256', $data, $secretKey);

        if (hash_equals($code, $generatedHMAC)) {
            http_response_code(200);
            echo 'HMAC jest poprawny!';
        } else {
            http_response_code(400);
            echo 'HMAC jest niepoprawny!';
        }
    } else {
        http_response_code(400);
        echo "Błąd: Nieprawidłowy format JSON.";
    }
    exit;
} else {
    http_response_code(405);
    echo "Błąd: Dozwolone tylko żądania POST.";
}

    function generateOrderId() {
        $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    
        return $randomString;
    }
    $orderid = generateOrderId();


    $nickname = $orderid;                                        // Ta zmienna służy do przechowywania pseudonimu gracza lub unikalnego identyfikatora zamówienia. Zaleca się użycie generatora identyfikatorów zamówień w celu zapewnienia lepszego śledzenia i integralności danych.
    
    $email = 'test@test.com';                                    // Ta zmienna przechowuje adres e-mail klienta. Jest to kluczowe dla komunikacji, takiej jak wysyłanie potwierdzeń płatności lub paragonów.
    
    $message = 'test';                                           // Ta zmienna może zawierać opcjonalną wiadomość związaną z transakcją, taką jak „VIP” lub inne informacje kontrolne istotne dla Twojego systemu.
    
    $amount = 100;                                               // Ta zmienna reprezentuje kwotę transakcji wyrażoną w groszach. Na przykład wartość 100 odpowiada 1 złotemu (zł).
    
    $cel = 'https://widgets.tipply.pl/TIPS_GOAL/';               // Ta zmienna przechowuje adres URL widgetu celu na platformie tipply do darowizn. Link ten powinien być aktualizowany w przypadku zmiany celu na owej platformie.
    
    $link = 'remsooo';                                           // Ta zmienna zawiera nazwę użytkownika lub identyfikator Tipply, który określa, gdzie ma zostać skierowana darowizna lub płatność w ramach platformy Tipply.
    
    $webhook = 'https://widgets.tipply.pl/TIP_ALERT/';           // Ta zmienna przechowuje adres URL widgetu alertów o napiwkach Tipply. Jest to zazwyczaj standardowy szablon dostarczany przez Tipply i służy do wywoływania alertów (np. na streamie) po pomyślnym przetworzeniu płatności.
    
    $accepted = 'http://localhost/submit_form.php';              // Ta zmienna określa adres URL, pod który zostaną przesłane dane o sukcesie płatności po przetworzeniu transakcji. Twój system powinien obsłużyć dane otrzymane z tego punktu końcowego, aby potwierdzić i przetworzyć udane płatności.
    
    $method = 'psc';                                             // Ta zmienna definiuje metodę płatności wybraną przez użytkownika. Przykłady obejmują psc (Paysafecard), paypal, cashbill_blik lub cashbill.


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

    $ch = curl_init('http://api.remus.wtf/pay/submit');
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

            echo "<script>window.open('$location', '_blank');</script>";

            exit();
        }
    }

    curl_close($ch);
    echo "Nie udało się znaleźć nagłówka Location. Odpowiedź serwera: $response";
?>