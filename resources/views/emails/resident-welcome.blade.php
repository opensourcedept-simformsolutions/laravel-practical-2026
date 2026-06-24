<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome to SocietyMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            padding: 20px;
        }

        .header {
            background: #0d6efd;
            color: white;
            padding: 16px;
            border-radius: 4px 4px 0 0;
        }

        .body {
            border: 1px solid #ddd;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 4px 4px;
            background: #fff;
        }

        .button {
            display: inline-block;
            background: #0d6efd;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 15px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .info-table th {
            background: #f8f9fa;
            width: 30%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Welcome to SocietyMS</h2>
        </div>

        <div class="body">
            <p>Dear {{ $user->name }},</p>

            <p>
                Your resident account has been created successfully in the SocietyMS portal.
            </p>

            <table class="info-table">
                <tr>
                    <th>Resident Name</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email Address</th>
                    <td>{{ $user->email }}</td>
                </tr>
            </table>

            <p>
                To activate your account and create your password, please click the button below:
            </p>

            <p style="text-align: center;">
                <a href="{{ $url }}" class="button">
                    Set Password
                </a>
            </p>

            <p>
                After setting your password, you can log in to the Society Portal and access your resident dashboard.
            </p>

            <p>
                If you did not expect this email, please contact the society administration.
            </p>

            <p>Thank you,<br>SocietyMS Team</p>
        </div>
    </div>
</body>

</html>
