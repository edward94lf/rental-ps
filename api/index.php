<?php
/**
 * RENTAL FADJAR - VERSI SKAKMAT BYPASS
 */

$accessId  = "wrhk7mwuy98hgcvyhgds"; 
$secretKey = "af864af877d349bf886e8397eff27b59";
$deviceId  = "a3c8e608901a306ff7ytyk";
$baseUrl   = "https://tuyasg.com"; 

function panggil_tuya_sakti($url, $method, $body = "", $token = "") {
    global $accessId, $secretKey, $baseUrl;
    $t = round(microtime(true) * 1000);
    $contentHash = hash("sha256", $body);
    $stringToSign = $accessId . $token . $t . $method . "\n" . $contentHash . "\n\n" . $url;
    $sign = strtoupper(hash_hmac("sha256", $stringToSign, $secretKey));

    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    
    // --- JURUS ANTI MACET ---
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Abaikan sertifikat SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Abaikan kecocokan host
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Paksa IPv4
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    // ------------------------

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "client_id: $accessId", "sign: $sign", "t: $t",
        "sign_method: HMAC-SHA256", "access_token: $token", "Content-Type: application/json"
    ]);

    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return $res ? json_decode($res, true) : ["msg" => "Koneksi Macet: $err"];
}

$pesan = "Siap...";
if (isset($_GET['aksi'])) {
    // 1. AMBIL TOKEN
    $resToken = panggil_tuya_sakti("/v1.0/token?grant_type=1", "GET");
    if (isset($resToken['result']['access_token'])) {
        $token = $resToken['result']['access_token'];
        
        // 2. KIRIM COMMAND (Switch untuk kategori tdq)
        $nyala = ($_GET['aksi'] == 'on') ? true : false;
        $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
        $resCmd = panggil_tuya_sakti("/v1.0/devices/$deviceId/commands", "POST", $body, $token);
        
        if (isset($resCmd['success']) && $resCmd['success']) {
            $pesan = "CEKLEK! BERHASIL DI" . strtoupper($_GET['aksi']);
        } else {
            $pesan = "GAGAL COMMAND: " . ($resCmd['msg'] ?? "Ditolak");
        }
    } else {
        $pesan = "GAGAL TOKEN: " . ($resToken['msg'] ?? "Server Singapore Gak Nyaut");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BILLING CLOUD FADJAR</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #121212; color: #fff; padding: 20px; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 20px; display: inline-block; border: 2px solid #333; }
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; color: white; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 20px; color: #00ff00; font-weight: bold; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h2>REMOTE CLOUD VERCEL</h2>
        <a href="?aksi=on" class="btn on">ON</a>
        <a href="?aksi=off" class="btn off">OFF</a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
