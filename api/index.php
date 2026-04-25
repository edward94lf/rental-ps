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
    $body = json_encode(["commands" => [["code" => "switch", "value" => $nyala]]]);
    $contentHash = hash("sha256", $body);
    $stringToSign = $accessId . $t . "POST\n" . $contentHash . "\n\n" . $url;
    $sign = strtoupper(hash_hmac("sha256", $stringToSign, $secretKey));

    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "client_id: $accessId", "sign: $sign", "t: $t",
        "sign_method: HMAC-SHA256", "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    $resArr = json_decode($result, true);
    curl_close($ch);

    if (isset($resArr['success']) && $resArr['success']) {
        $pesan = "CEKLEK! BERHASIL " . strtoupper($_GET['aksi']);
    } else {
        $pesan = "GAGAL: " . ($resArr['msg'] ?? "Error");
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
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; text-decoration: none; }
        .on { background: #2ecc71; color: white; }
        .off { background: #e74c3c; color: white; }
        .status { margin-top: 20px; color: #00ff00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>KONTROL PS 1</h2>
        <a href="?aksi=on" class="btn on">ON (NYALAKAN)</a>
        <a href="?aksi=off" class="btn off">OFF (MATIKAN)</a>
        <div class="status"><?php echo $pesan; ?></div>
    </div>
</body>
</html>
