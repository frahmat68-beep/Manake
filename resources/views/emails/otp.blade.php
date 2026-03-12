<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Manake</title>
</head>
<body style="margin:0; padding:0; background:#edf2fb; font-family:'Plus Jakarta Sans', Arial, Helvetica, sans-serif; color:#13233d;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#edf2fb; margin:0; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; margin:0 auto;">
                    <tr>
                        <td style="padding:0 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-radius:32px; overflow:hidden; background:#ffffff; border:1px solid #dbe4f3; box-shadow:0 24px 60px rgba(15, 23, 42, 0.12);">
                                <tr>
                                    <td style="padding:32px 32px 20px; background:linear-gradient(135deg, #0f1a31 0%, #183a90 58%, #2a56e8 100%);">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:13px; letter-spacing:0.24em; text-transform:uppercase; color:#dbe7ff; font-weight:700;">
                                                    Manake Security
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:12px; font-size:34px; line-height:1.1; color:#ffffff; font-weight:800;">
                                                    Kode OTP akun kamu
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:10px; font-size:15px; line-height:1.7; color:#d8e4ff;">
                                                    Halo {{ $recipientName }}, gunakan kode di bawah ini untuk melanjutkan login atau verifikasi akun Manake.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:28px 32px 10px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center">
                                                    <div style="display:inline-block; min-width:250px; border-radius:24px; background:linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%); border:1px solid #d7e3ff; box-shadow:inset 0 1px 0 rgba(255,255,255,0.72); padding:18px 28px;">
                                                        <div style="font-size:12px; letter-spacing:0.22em; text-transform:uppercase; color:#6a7b97; font-weight:700; margin-bottom:10px;">
                                                            One-Time Password
                                                        </div>
                                                        <div style="font-size:40px; line-height:1; letter-spacing:0.28em; color:#2048d5; font-weight:800; text-indent:0.28em;">
                                                            {{ $otp }}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:20px; font-size:14px; line-height:1.75; color:#4b5f7d;">
                                                    Kode berlaku sekitar <strong style="color:#13233d;">{{ $expiresInMinutes }} menit</strong> dan hanya bisa dipakai sekali. Jangan bagikan kode ini ke siapa pun, termasuk admin.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 32px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate; border-spacing:0;">
                                            <tr>
                                                <td style="padding:16px 18px; border:1px solid #e2eaf7; border-radius:20px; background:#f8fbff; font-size:13px; line-height:1.7; color:#5a6f8f;">
                                                    Jika kamu tidak merasa meminta OTP ini, abaikan email ini dan segera ganti kata sandi akun kamu.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                                <tr>
                                    <td align="center" style="padding:0 24px; font-size:12px; line-height:1.7; color:#7b8ca8;">
                                        Email ini dikirim otomatis oleh Manake. Mohon tidak membalas email ini.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
