<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visitor Entry Notification</title>
  <style>
    body { font-family: Arial, sans-serif; color: #333; }
    .container { padding: 20px; }
    .header { background: #198754; color: white; padding: 16px; border-radius: 4px 4px 0 0; }
    .body { border: 1px solid #ddd; border-top: none; padding: 16px; border-radius: 0 0 4px 4px; }
    .table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    .table th { background: #f8f9fa; width: 30%; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2 style="margin: 0;">Visitor Entry Notification</h2>
    </div>
    <div class="body">
      <p>Dear Resident,</p>
      <p>A visitor has entered the society premises.</p>

      <table class="table">
        <tr>
          <th>Visitor Name</th>
          <td>{{ $visitorLog->visitor?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
          <th>Phone Number</th>
          <td>{{ $visitorLog->visitor?->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
          <th>Purpose</th>
          <td>{{ $visitorLog->purpose ?? 'N/A' }}</td>
        </tr>
        <tr>
          <th>Flat</th>
          <td>
            {{ $visitorLog->flat?->wing ?? 'N/A' }} -
            {{ $visitorLog->flat?->floor ?? 'N/A' }} -
            {{ $visitorLog->flat?->flat_number ?? 'N/A' }}
          </td>
        </tr>
        <tr>
          <th>Entry Time</th>
          <td>{{ $visitorLog->entry_time?->format('d M Y h:i A') ?? 'N/A' }}</td>
        </tr>
      </table>

      <p>Thank you.</p>
    </div>
  </div>
</body>
</html>
