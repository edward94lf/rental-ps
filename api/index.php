<?php
$accessId  = "wrhk7mwuy98hgcvyhgds"; 
$secretKey = "af864af877d349bf886e8397eff27b59";
$deviceId  = "a3c8e608901a306ff7ytyk";
$baseUrl   = "https://tuyaus.com"; // PAKAI JALUR GLOBAL US

function panggil_tuya($url, $method, $body = "", $token = "") {
    global $accessId, $secretKey, $baseUrl;
    $t = round(microtime(true) * 1000);
    $contentHash = hash("sha256", $body);
    $stringToSign = $accessId . $token . $t . $method . "\n" . $contentHash . "\n\n" . $url;
    $sign = strtoupper(hash_hmac("sha256", $stringToSign, $secretKey));

    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "client_id: $accessId", "sign: $sign", "t: $t",
        "sign_method: HMAC-SHA256", "access_token: $token", "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Tunggu lebih lama biar gak macet
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$pesan = "Siap...";
if (isset($_GET['aksi'])) {
    $resToken = panggil_tuya("/v1.0/token?grant_type=1", "GET");
    if (isset($resToken['result']['access_token'])) {
        $token = $resToken['result']['access_token'];
        $nyala = ($_GET['aksi'] == 'on') ? true : false;
        $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
        $resCmd = panggil_tuya("/v1.0/devices/$deviceId/commands", "POST", $body, $token);
        
        if (isset($resCmd['success']) && $resCmd['success']) {
            $pesan = "CEKLEK! BERHASIL DI" . strtoupper($_GET['aksi']);
        } else {
            $pesan = "GAGAL PERINTAH: " . ($resCmd['msg'] ?? "Ditolak");
        }
    } else {
        $pesan = "TOKEN GAGAL: " . ($resToken['msg'] ?? "Koneksi Global Macet");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BILLING PS FADJAR</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #000; color: #fff; padding: 20px; }
        .card { background: #111; padding: 30px; border-radius: 20px; display: inline-block; border: 1px solid #333; }
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; color: white; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 20px; color: #00ff00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>REMOTE GLOBAL VERCEL</h2>
        <a href="?aksi=on" class="btn on">ON</a>
        <a href="?aksi=off" class="btn off">OFF</a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
