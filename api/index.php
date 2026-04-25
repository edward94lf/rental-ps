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
    
    // Pakai waktu server Vercel saja, biasanya Singapore mau terima selisih dikit
    $t = round(microtime(true) * 1000);
    
    // --- LANGKAH 1: AMBIL TOKEN ---
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
        
        // --- LANGKAH 2: KIRIM PERINTAH ---
        $urlCmd = "/v1.0/devices/$deviceId/commands";
        $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
        $signCmd = strtoupper(hash_hmac("sha256", $accessId . $token . $t . "POST\n" . hash("sha256", $body) . "\n\n" . $urlCmd, $secretKey));

        $ch = curl_init($baseUrl . $urlCmd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "client_id: $accessId", "sign: $signCmd", "t: $t", 
            "access_token: $token", "sign_method: HMAC-SHA256", "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resCmd = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($resCmd['success']) && $resCmd['success']) {
            $pesan = "CEKLEK! BERHASIL " . strtoupper($_GET['aksi']);
        } else {
            $pesan = "PERINTAH GAGAL: " . ($resCmd['msg'] ?? "Ditolak");
        }
    } else {
        $pesan = "TOKEN GAGAL: " . ($resToken['msg'] ?? "Cek AccessID/SecretKey");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; text-align: center; background: #121212; color: #fff; padding: 20px; }
        .btn { padding: 30px; font-size: 25px; margin: 10px; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; color: white; display: block; margin-left: auto; margin-right: auto; text-decoration: none; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 25px; color: #00ff00; font-weight: bold; font-family: monospace; }
    </style>
</head>
<body>
    <h2>REMOTE CLOUD VERCEL</h2>
    <a href="?aksi=on" class="btn on">ON (NYALAKAN)</a>
    <a href="?aksi=off" class="btn off">OFF (MATIKAN)</a>
    <div class="status"><?php echo $pesan; ?></div>
</body>
</html>
