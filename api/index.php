<?php
// 1. DATA RESMI ABANG (PASTIKAN NO SPASI)
$accessId  = "wrhk7mwuy98hgcvyhgds"; 
$secretKey = "af864af877d349bf886e8397eff27b59";
$deviceId  = "a3c8e608901a306ff7ytyk";
$baseUrl   = "https://tuyasg.com"; 

$pesan = "Siap...";

if (isset($_GET['aksi'])) {
    $nyala = ($_GET['aksi'] == 'on') ? true : false;
    date_default_timezone_set('Asia/Jakarta');
    
    // Milidetik Sekarang
    $t = round(microtime(true) * 1000);
    
    // --- LANGKAH 1: MINTA TOKEN ---
    $urlToken = "/v1.0/token?grant_type=1";
    $signToken = strtoupper(hash_hmac("sha256", $accessId . $t . "GET\n" . hash("sha256", "") . "\n\n" . $urlToken, $secretKey));

    $ch = curl_init($baseUrl . $urlToken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["client_id: $accessId", "sign: $signToken", "t: $t", "sign_method: HMAC-SHA256"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resToken = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($resToken['result']['access_token'])) {
        $token = $resToken['result']['access_token'];
        
        // --- LANGKAH 2: MATIIN TV ---
        $urlCmd = "/v1.0/devices/$deviceId/commands";
        $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
        $signCmd = strtoupper(hash_hmac("sha256", $accessId . $token . $t . "POST\n" . hash("sha256", $body) . "\n\n" . $urlCmd, $secretKey));

        $ch = curl_init($baseUrl . $urlCmd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "client_id: $accessId", "access_token: $token", "sign: $signCmd", "t: $t", 
            "sign_method: HMAC-SHA256", "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resCmd = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($resCmd['success']) && $resCmd['success']) {
            $pesan = "CEKLEK! BERHASIL DI" . strtoupper($_GET['aksi']);
        } else {
            $pesan = "GAGAL: " . ($resCmd['msg'] ?? "Ditolak");
        }
    } else {
        $pesan = "TOKEN GAGAL: Cek ID/Secret Abang!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BILLING PS FADJAR</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #121212; color: #fff; padding: 20px; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 20px; display: inline-block; border: 2px solid #333; }
        .btn { padding: 30px; font-size: 25px; margin: 15px 0; width: 100%; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; color: white; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 20px; color: #00ff00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>KONTROL PS 1</h1>
        <hr style="border: 0.5px solid #444; margin-bottom: 25px;">
        <a href="?aksi=on" style="text-decoration: none;"><button class="btn on">ON (NYALAKAN)</button></a>
        <a href="?aksi=off" style="text-decoration: none;"><button class="btn off">OFF (MATIKAN)</button></a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
