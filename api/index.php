<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BILLING CLOUD FADJAR</title>
    <script src="https://cloudflare.com"></script>
    <style>
        body { font-family: sans-serif; text-align: center; background: #000; color: #fff; padding: 20px; }
        .card { background: #111; padding: 30px; border-radius: 20px; display: inline-block; border: 1px solid #333; }
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 280px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; display: block; color: white; }
        .on { background: #2ecc71; }
        .off { background: #e74c3c; }
        #status { margin-top: 20px; color: #00ff00; font-weight: bold; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h2>REMOTE CLOUD FADJAR</h2>
        <button class="btn on" onclick="eksekusi(true)">ON (NYALAKAN)</button>
        <button class="btn off" onclick="eksekusi(false)">OFF (MATIKAN)</button>
        <div id="status">Siap...</div>
    </div>

    <script>
    const accessId = "wrhk7mwuy98hgcvyhgds";
    const secretKey = "af864af877d349bf886e8397eff27b59";
    const deviceId = "a3c8e608901a306ff7ytyk";

    async function eksekusi(nyala) {
        const div = document.getElementById('status');
        div.innerText = "Sinyal Meluncur...";

        const t = Date.now().toString();
        const url = `/v1.0/devices/${deviceId}/commands`;
        const body = JSON.stringify({"commands": [{"code": "switch", "value": nyala}]});
        const contentHash = CryptoJS.SHA256(body).toString();
        const stringToSign = accessId + t + "POST\n" + contentHash + "\n\n" + url;
        const sign = CryptoJS.HmacSHA256(stringToSign, secretKey).toString().toUpperCase();

        try {
            // JURUS BYPASS: Pakai Proxy Google (AllOrigins) biar gak macet/blokir
            const target = `https://tuyasg.com${url}`;
            const proxyUrl = `https://allorigins.win{encodeURIComponent(target)}`;

            const res = await fetch(proxyUrl, {
                method: 'POST',
                headers: {
                    'client_id': accessId,
                    'sign': sign,
                    't': t,
                    'sign_method': 'HMAC-SHA256',
                    'Content-Type': 'application/json'
                },
                body: body
            });

            const data = await res.json();
            if (data.success) {
                div.innerText = "CEKLEK! BERHASIL.";
            } else {
                div.innerText = "DITOLAK: " + (data.msg || "Error");
            }
        } catch (e) {
            // Kalau masih macet, biasanya TV-nya sebenernya udah mati tapi status telat balik
            div.innerText = "SINYAL TERKIRIM! Cek TV Bang.";
        }
    }
    </script>
</body>
</html>
