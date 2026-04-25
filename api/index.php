<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BILLING CLOUD FADJAR</title>
    <script src="https://cloudflare.com"></script>
    <style>
        body { font-family: sans-serif; text-align: center; background: #121212; color: #fff; padding: 20px; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 20px; display: inline-block; border: 2px solid #333; }
        .btn { padding: 25px; font-size: 24px; margin: 15px 0; width: 100%; max-width: 300px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; }
        .on { background: #2ecc71; color: white; }
        .off { background: #e74c3c; color: white; }
        #status { margin-top: 20px; padding: 15px; background: #222; color: #00ff00; font-weight: bold; border-radius: 10px; min-height: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>KONTROL PS 1</h1>
        <p style="color: #888;">Cloud Mode (PC Boleh Mati)</p>
        <button class="btn on" onclick="eksekusi(true)">ON (NYALAKAN)</button>
        <button class="btn off" onclick="eksekusi(false)">OFF (MATIKAN)</button>
        <div id="status">Siap...</div>
    </div>

    <script>
    // DATA RESMI ABANG
    const accessId = "wrhk7mwuy98hgcvyhgds";
    const secretKey = "af864af877d349bf886e8397eff27b59";
    const deviceId = "a3c8e608901a306ff7ytyk";

    async function eksekusi(nyala) {
        const div = document.getElementById('status');
        div.innerText = "Membuka Jalur Singapore...";

        const t = Date.now().toString();
        const url = `/v1.0/devices/${deviceId}/commands`;
        const body = JSON.stringify({"commands": [{"code": "switch", "value": nyala}]});
        
        // Hitung Enkripsi
        const contentHash = CryptoJS.SHA256(body).toString();
        const stringToSign = accessId + t + "POST\n" + contentHash + "\n\n" + url;
        const sign = CryptoJS.HmacSHA256(stringToSign, secretKey).toString().toUpperCase();

        try {
            // JEMBATAN PROXY AGAR TIDAK NYANGKUT
            const proxy = "https://herokuapp.com";
            const target = "https://tuyasg.com" + url;

            const res = await fetch(proxy + target, {
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
                div.innerText = "CEKLEK! BERHASIL " + (nyala ? "NYALA" : "MATI");
            } else {
                div.innerText = "GAGAL: " + data.msg;
                // Jika butuh izin proxy
                if(data.msg && data.msg.includes("Missing required request header")) {
                    window.open("https://herokuapp.comcorsdemo", "_blank");
                }
            }
        } catch (e) {
            div.innerText = "Klik 'Request Access' lalu coba lagi!";
            window.open("https://herokuapp.comcorsdemo", "_blank");
        }
    }
    </script>
</body>
</html>
