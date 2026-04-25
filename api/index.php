<?php
// 1. DATA RESMI (DIBERSIHKAN OTOMATIS DARI SPASI)
$accessId  = trim("wrhk7mwuy98hgcvyhgds"); 
$secretKey = trim("af864af877d349bf886e8397eff27b59");
$deviceId  = trim("a3c8e608901a306ff7ytyk");
$baseUrl   = "https://tuyasg.com"; 

function panggil_tuya($url, $method, $body = "", $token = "") {
    global $accessId, $secretKey, $baseUrl;
    
    // Ambil waktu server Tuya biar gak selisih jam
    $t = round(microtime(true) * 1000);
    $contentHash = hash("sha256", $body);
    
    // RUMUS SIGNATURE TERBARU (SANGAT KETAT)
    $stringToSign = $accessId . $token . $t . $method . "\n" . $contentHash . "\n\n" . $url;
    $sign = strtoupper(hash_hmac("sha256", $stringToSign, $secretKey));

    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "client_id: $accessId",
        "sign: $sign",
        "t: $t",
        "sign_method: HMAC-SHA256",
        "access_token: $token",
        "Content-Type: application/json"
    ]);

    // BYPASS KEAMANAN BIAR GAK BLOKIR
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

$pesan = "Siap...";
if (isset($_GET['aksi'])) {
    // 1. MINTA TOKEN (KUNCI)
    $resToken = panggil_tuya("/v1.0/token?grant_type=1", "GET");
    
    if (isset($resToken['result']['access_token'])) {
        $token = $resToken['result']['access_token'];
        
        // 2. KIRIM PERINTAH
        $nyala = ($_GET['aksi'] == 'on') ? true : false;
        $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
        $resCmd = panggil_tuya("/v1.0/devices/$deviceId/commands", "POST", $body, $token);
        
        if (isset($resCmd['success']) && $resCmd['success']) {
            $pesan = "CEKLEK! BERHASIL " . strtoupper($_GET['aksi']);
        } else {
            $pesan = "GAGAL PERINTAH: " . ($resCmd['msg'] ?? "Ditolak");
        }
    } else {
        // Tampilkan pesan asli dari Singapore biar kita tahu salahnya dimana
        $errMsg = isset($resToken['msg']) ? $resToken['msg'] : "Cek AccessID/SecretKey";
        $errCode = isset($resToken['code']) ? $resToken['code'] : "NoCode";
        $pesan = "TOKEN GAGAL: $errMsg (Kode: $errCode)";
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
        .card { background: #1e1e1e; padding: 30px; border-radius: 20px; display: inline-block; border: 2px solid #444; }
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; color: white; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 20px; color: #00ff00; font-family: monospace; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>REMOTE CLOUD V6</h1>
        <a href="?aksi=on" class="btn on">NYALAKAN (ON)</a>
        <a href="?aksi=off" class="btn off">MATIKAN (OFF)</a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
