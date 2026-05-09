<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #444; }
        .container { width: 100%; max-width: 650px; margin: 0 auto; border: 1px solid #eee; padding: 25px; border-radius: 4px; }
        .header { font-size: 22px; color: #1e293b; font-weight: bold; margin-bottom: 20px; border-bottom: 3px solid #1F6AAE; padding-bottom: 12px; }
        
        /* Credential Table Styling */
        .credential-table { width: 100%; border-collapse: collapse; margin: 25px 0; }
        .credential-table th { background-color: #334155; color: #ffffff; text-align: left; padding: 14px; width: 30%; border: 1px solid #334155; }
        .credential-table td { padding: 14px; border: 1px solid #e2e8f0; background-color: #f8fafc; font-family: 'Courier New', monospace; font-size: 15px; color: #1e293b; }
        
        .security-note { margin: 20px 0; font-size: 14px; padding: 10px; background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; }
        .signature-divider { border-top: 1px solid #e2e8f0; margin: 30px 0 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Internship Account Credentials</div>
        
        <p>Dear <strong>{{ $intern->name }}</strong>,</p>
        <p>Welcome to <strong>TechStrota</strong>! Your account for the Internship Administration and Performance Evaluation System (IAPES) is now active. Please use the credentials below to access the platform.</p>

        <table class="credential-table">
            <tr>
                <th>Username</th>
                <td>{{ $intern->username }}</td>
            </tr>
            <tr>
                <th>Temporary Password</th>
                <td>{{ $password }}</td>
            </tr>
        </table>

        <div class="security-note">
            <strong>Security Note:</strong> For your protection, please log in and change your password immediately upon your first access.
        </div>

        <div class="signature-divider"></div>

        <table cellpadding="0" cellspacing="0" border="0" style="font-family: Arial, sans-serif; font-size:13px; color:#333; line-height: 1.4;">
            <tr>
                <td style="vertical-align:middle; padding-right:15px;">
                    <img src="https://techstrota.com/_next/image?url=%2Flogo.png&w=384&q=75" alt="TechStrota Logo" width="150" style="display:block; max-width:150px; height:auto;" />
                </td>
                <td style="vertical-align:middle; border-left: 1px solid #cbd5e1; padding-left: 15px;">
                    <strong style="font-size:16px;">
                        <a href="https://techstrota.com" style="text-decoration:none; color:#1F6AAE;">TechStrota</a>
                    </strong><br>
                    <a href="https://techstrota.com" style="color:#1F6AAE; text-decoration:none;">Website</a> |
                    <a href="https://linkedin.com/company/techstrota" style="color:#1F6AAE; text-decoration:none;">LinkedIn</a> |
                    <a href="https://wa.me/918128840055" style="color:#16a34a; text-decoration:none;">WhatsApp</a>
                    <br>
                    Mobile: <a href="tel:+918128840055" style="color:#1F6AAE; text-decoration:none;">+91 8128840055</a><br>
                    Email: <a href="mailto:sales@techstrota.com" style="color:#1F6AAE; text-decoration:none;">sales@techstrota.com</a><br>
                    <span style="color: #64748b; font-size: 11px;">
                        CIN: GJ240114897 | 503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat, <b>India</b>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>