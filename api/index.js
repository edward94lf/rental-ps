const crypto = require('crypto');

export default async function handler(req, res) {
    const { aksi } = req.query;
    if (!aksi) return res.send("Gunakan ?aksi=on atau off");

    const accessId = "wrhk7mwuy98hgcvyhgds";
    const secretKey = "af864af877d349bf886e8397eff27b59";
    const deviceId = "a3c8e608901a306ff7ytyk";
    const baseUrl = "https://tuyasg.com";

    try {
        // 1. Ambil Waktu Resmi Tuya (Biar Gak Gagal Jam)
        const timeRes = await fetch(`${baseUrl}/v1.0/statistics/time`);
        const { result: t } = await timeRes.json();

        // 2. Minta Token
        const tokenUrl = "/v1.0/token?grant_type=1";
        const signToken = crypto.createHmac('sha256', secretKey)
            .update(accessId + t + "GET\n" + crypto.createHash('sha256').update("").digest('hex') + "\n\n" + tokenUrl)
            .digest('hex').toUpperCase();

        const resToken = await fetch(baseUrl + tokenUrl, {
            headers: { 'client_id': accessId, 'sign': signToken, 't': t.toString(), 'sign_method': 'HMAC-SHA256' }
        });
        const dataToken = await resToken.json();
        const token = dataToken.result.access_token;

        // 3. Kirim Perintah
        const nyala = aksi === 'on';
        const cmdUrl = `/v1.0/devices/${deviceId}/commands`;
        const body = JSON.stringify({"commands": [{"code": "switch", "value": nyala}]});
        const contentHash = crypto.createHash('sha256').update(body).digest('hex');
        const signCmd = crypto.createHmac('sha256', secretKey)
            .update(accessId + token + t + "POST\n" + contentHash + "\n\n" + cmdUrl)
            .digest('hex').toUpperCase();

        const resFinal = await fetch(baseUrl + cmdUrl, {
            method: 'POST',
            headers: {
                'client_id': accessId, 'sign': signCmd, 't': t.toString(),
                'access_token': token, 'sign_method': 'HMAC-SHA256', 'Content-Type': 'application/json'
            },
            body: body
        });
        const dataFinal = await resFinal.json();

        if (dataFinal.success) {
            res.send(`<h1>CEKLEK! BERHASIL ${aksi.toUpperCase()}</h1>`);
        } else {
            res.send(`<h1>DITOLAK: ${dataFinal.msg}</h1>`);
        }
    } catch (e) {
        res.send("<h1>ERROR KONEKSI</h1>");
    }
}
