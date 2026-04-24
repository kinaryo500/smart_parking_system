<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>

<body style="margin:0; padding:0; background:#f4f6fb; font-family:Arial, sans-serif;">

<div style="max-width:600px; margin:30px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.1);">

    <div style="background:#0061ff; padding:20px; text-align:center; color:#fff;">
        <h2 style="margin:0;">Smart Parking System</h2>
        <p style="margin:5px 0 0;">Reset Password Akun Anda</p>
    </div>

    <div style="padding:30px; color:#333;">

        <h3 style="margin-top:0;">Halo</h3>

        <p>Kami menerima permintaan reset password untuk akun Anda.</p>

        <p>
            Untuk keamanan, link reset password ini hanya berlaku
            <strong>10 menit</strong> sejak email ini dikirim.
        </p>

        <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>

        <div style="text-align:center; margin:30px 0;">
            <a href="{{ url('/reset-password/'.$token.'?email='.$email) }}"
               style="background:#0061ff; color:#fff; padding:12px 25px; text-decoration:none; border-radius:8px; display:inline-block; font-weight:bold;">
                Reset Password
            </a>
        </div>

        <p style="font-size:13px; color:#777;">
            Jika Anda tidak meminta reset password, abaikan email ini.
        </p>

        <p style="font-size:13px; color:#777;">
            Demi keamanan, jangan bagikan link ini kepada siapapun.
        </p>

    </div>

    <div style="background:#f0f4ff; padding:15px; text-align:center; font-size:12px; color:#666;">
        © {{ date('Y') }} Smart Parking System
    </div>

</div>

</body>
</html>