<?php
$accessId  = "wrhk7mwuy98hgcvyhgds"; 
$secretKey = "af864af877d349bf886e8397eff27b59";
$deviceId  = "a3c8e608901a306ff7ytyk";
$baseUrl   = "https://tuyasg.com"; 

$pesan = "Siap...";

if (isset($_GET['aksi'])) {
    $nyala = ($_GET['aksi'] == 'on') ? true : false;
    date_default_timezone_set('Asia/Jakarta');
    $t = round(microtime(true) * 1000);
    $url = "/v1.0/devices/$deviceId/commands";

    // Kita kirim switch dan switch_1 sekaligus biar gak meleset
    $body = json_encode(["commands" => [
        ["code" => "switch", "value" => $nyala],
        ["code" => "switch_1", "value" => $nyala]
    ]]);
    
    $contentHash = hash("sha256", $body);
    // Rumus Sign Tanpa Token (Jalur Ekspres)
    $stringToSign = $accessId . $t . "POST\n" . $contentHash . "\n\n" . $url;
    $sign = strtoupper(hash_hmac("sha256", $stringToSign, $secretKey));

    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "client_id: $accessId",
        "sign: $sign",
        "t: $t",
        "sign_method: HMAC-SHA256",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $resArr = json_decode($result, true);
    curl_close($ch);

    if (isset($resArr['success']) && $resArr['success']) {
        $pesan = "CEKLEK! BERHASIL " . strtoupper($_GET['aksi']);
    } else {
        // Tampilkan pesan asli biar kita tau salahnya dimana
        $msg = isset($resArr['msg']) ? $resArr['msg'] : "Gagal Total";
        $code = isset($resArr['code']) ? $resArr['code'] : "??";
        $pesan = "ERROR: $msg ($code)";
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
        .card { border: 2px solid #333; padding: 30px; border-radius: 20px; display: inline-block; background: #111; }
        .btn { padding: 25px; font-size: 22px; margin: 10px; width: 250px; border-radius: 12px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; color: white; }
        .on { background: #2ecc71; } .off { background: #e74c3c; }
        .status { margin-top: 20px; color: #00ff00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>REMOTE VERCEL V5</h2>
        <a href="?aksi=on" class="btn on">ON</a>
        <a href="?aksi=off" class="btn off">OFF</a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
