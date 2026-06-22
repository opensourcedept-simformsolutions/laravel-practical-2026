<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>

    <h2>Welcome to SocietyMS</h2>

    <p>
        Hello {{ $user->name }},
    </p>

    <p>
        Your resident account has been created successfully.
    </p>

    <p>
        Please click the button below to set your password.
    </p>

    <p>
        <a href="{{ $url }}"
           style="
                background:#0d6efd;
                color:white;
                padding:12px 20px;
                text-decoration:none;
                border-radius:6px;
           ">
            Set Password
        </a>
    </p>

    <p>
        After setting your password you can login to the Society Portal.
    </p>

</body>
</html>