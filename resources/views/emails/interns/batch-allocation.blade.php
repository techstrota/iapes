<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Batch Allocation & Schedule</title>
    <!--[if mso]>
    <xml>
      <o:OfficeDocumentSettings>
        <o:AllowPNG/>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f9; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; }
        table { border-spacing: 0; border-collapse: collapse; }
        td { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        p { margin: 0 0 15px; font-size: 15px; line-height: 1.6; color: #444444; }
        a { color: #1F6AAE; text-decoration: none; }
        a:hover { text-decoration: underline; }
        
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7f9; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #1F6AAE; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px; }
        .content { padding: 35px 40px; }
        
        /* Details Table Styling */
        .details-table { width: 100%; border-collapse: collapse; margin: 25px 0; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .details-table th { background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px; }
        .details-table td { padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a; }
        .details-table tr:last-child th, .details-table tr:last-child td { border-bottom: none; }
        .badge-code { font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: bold; color: #1F6AAE; background-color: #f0f7ff; padding: 3px 8px; border-radius: 4px; border: 1px solid #bfdbfe; }
        
        .notice-card { background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 16px 20px; margin: 22px 0; }
        .notice-card h4 { margin: 0 0 6px; font-size: 13px; color: #92400e; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .notice-card p { margin: 0; font-size: 14px; color: #92400e; line-height: 1.5; }
        
        .portal-card { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 18px 22px; margin: 22px 0; }
        .portal-card h4 { margin: 0 0 6px; font-size: 14px; color: #166534; font-weight: 700; }
        .portal-card p { margin: 0 0 10px; font-size: 13.5px; color: #166534; line-height: 1.5; }
        
        .btn-portal { display: inline-block; background-color: #1F6AAE; color: #ffffff !important; font-size: 13.5px; font-weight: 600; padding: 10px 22px; border-radius: 6px; text-decoration: none; margin-top: 6px; }
        
        .footer { padding: 30px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        
        /* Signature Styles */
        .sig-container { width: 100%; }
        .sig-logo-td { padding-right: 25px; width: 140px; vertical-align: top; }
        .sig-text-td { vertical-align: top; font-size: 13px; line-height: 1.6; color: #475569; }
        .sig-text-td strong { color: #1e293b; }
        .wa { color: #16a34a !important; font-weight: 600; }
        
        /* Dark Mode overrides for Gmail */
        @media (prefers-color-scheme: dark) {
            .main { background-color: #1e293b !important; }
            .content p { color: #cbd5e1 !important; }
            .details-table { border-color: #334155 !important; }
            .details-table th { background-color: #0f172a !important; color: #94a3b8 !important; border-color: #334155 !important; }
            .details-table td { background-color: #1e293b !important; color: #f8fafc !important; border-color: #334155 !important; }
            .badge-code { background-color: #1e3a8a !important; color: #93c5fd !important; border-color: #2563eb !important; }
            .notice-card { background-color: #451a03 !important; border-color: #78350f !important; border-left-color: #f59e0b !important; }
            .notice-card h4 { color: #fcd34d !important; }
            .notice-card p { color: #fde68a !important; }
            .portal-card { background-color: #064e3b !important; border-color: #065f46 !important; }
            .portal-card h4 { color: #a7f3d0 !important; }
            .portal-card p { color: #d1fae5 !important; }
            .footer { background-color: #0f172a !important; border-color: #334155 !important; }
            .sig-text-td { color: #94a3b8 !important; }
            .sig-text-td strong { color: #f8fafc !important; }
        }
        
        /* Mobile Styles */
        @media screen and (max-width: 600px) {
            .wrapper { padding: 15px !important; }
            .main { border-radius: 12px !important; width: 100% !important; max-width: 100% !important; }
            .header { padding: 25px 20px !important; }
            .content { padding: 25px 20px !important; }
            .details-table th, .details-table td { padding: 10px 12px !important; }
            .footer { padding: 25px 20px !important; }
            .sig-logo-td, .sig-text-td { display: block !important; width: 100% !important; }
            .sig-logo-td { padding-right: 0 !important; padding-bottom: 20px !important; text-align: left !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f9;">
    <div class="wrapper" style="background-color: #f4f7f9; width: 100%;">
        <table class="main" align="center" style="background-color: #ffffff; margin: 0 auto; max-width: 600px; width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            
            <!-- Header -->
            <tr>
                <td class="header" style="background-color: #1F6AAE; padding: 30px 40px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 600;">Internship Batch & Schedule Allocation</h1>
                </td>
            </tr>
            
            <!-- Content -->
            <tr>
                <td class="content" style="padding: 35px 40px;">
                    <p style="margin-top: 0; font-size: 16px;">Dear <strong>{{ $intern->name }}</strong>,</p>
                    
                    <p>We are pleased to inform you that your internship batch and training schedule have been allocated at <strong>TechStrota</strong>. Please find the complete schedule and internship details outlined below:</p>
                    
                    <!-- Details Table -->
                    <table class="details-table" width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Intern ID</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a;">
                                <span class="badge-code">{{ $internCode }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Batch Name</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a; font-weight: 600;">
                                {{ $batch->batch_name }}
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Batch Timing</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a; font-weight: 600;">
                                {{ $batch->batch_timing ?: 'Schedule as per batch notification' }}
                                <div style="font-size: 12px; font-weight: normal; color: #1F6AAE; margin-top: 3px;">
                                    ⏰ {{ $shiftLabel }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Internship Duration</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a;">
                                {{ $durationDates }}
                            </td>
                        </tr>
                        @if (!empty($intern->internship_role ?: $intern->domain))
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Role / Domain</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a;">
                                {{ $intern->internship_role ?: $intern->domain }}
                            </td>
                        </tr>
                        @endif
                        @if ($intern->team)
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Assigned Project Team</th>
                            <td style="padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background-color: #ffffff; font-size: 14.5px; color: #0f172a;">
                                <strong>{{ $intern->team->team_name }}</strong>
                                @if ($intern->team->mentor_name)
                                    <div style="font-size: 12.5px; color: #475569; margin-top: 3px;">
                                        👤 Mentor: <strong>{{ $intern->team->mentor_name }}</strong>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th style="background-color: #f8fafc; color: #475569; text-align: left; padding: 13px 18px; width: 38%; border-right: 1px solid #e2e8f0; font-weight: 600; font-size: 13.5px;">Company</th>
                            <td style="padding: 13px 18px; background-color: #ffffff; font-size: 14.5px; color: #0f172a;">
                                <strong>Techstrota</strong> (Vadodara)
                            </td>
                        </tr>
                    </table>

                    @if (filled($customNote))
                    <!-- Additional Instructions Notice Card -->
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                        <tr>
                            <td class="notice-card" style="background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 14px 18px;">
                                <h4 style="margin: 0 0 5px; font-size: 13px; color: #92400e; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">📌 Instructions & Notes</h4>
                                <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.5; white-space: pre-line;">{{ $customNote }}</p>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <p style="margin-top: 20px; margin-bottom: 0;">If you have any questions or scheduling conflicts, please contact your coordinator or the HR department immediately.</p>
                    
                    <p style="margin-top: 25px; margin-bottom: 0;">Warm Regards,<br><strong style="color: #1F6AAE; font-size: 15px;">Team TechStrota</strong></p>
                </td>
            </tr>
            
            <!-- Footer Signature -->
            <tr>
                <td class="footer" style="padding: 30px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <table class="sig-container" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <!-- Logo (Left) -->
                            <td class="sig-logo-td" style="padding-right: 25px; width: 140px; vertical-align: top;">
                                <div style="background-color: #ffffff; padding: 10px; border-radius: 8px; display: inline-block; border: 1px solid #e2e8f0;">
                                    <a href="https://techstrota.com" target="_blank">
                                        <img src="https://techstrota.com/_next/image?url=%2Flogo.png&w=384&q=75" alt="TechStrota Logo" width="120" style="width: 120px; max-width: 120px; height: auto; display: block; border: none;" />
                                    </a>
                                </div>
                            </td>
                            
                            <!-- Text Details (Right) -->
                            <td class="sig-text-td" style="vertical-align: top; font-size: 13px; line-height: 1.6; color: #475569;">
                                <strong style="font-size: 16px;"><a href="https://techstrota.com" style="color: #1F6AAE; text-decoration: none;">TechStrota</a></strong>
                                <br>
                                <span style="font-size: 12px; margin-top: 4px; display: inline-block;">
                                    <a href="https://techstrota.com" style="color: #1F6AAE;">Website</a> &nbsp;|&nbsp; 
                                    <a href="https://linkedin.com/company/techstrota" style="color: #1F6AAE;">LinkedIn</a> &nbsp;|&nbsp; 
                                    <a href="https://wa.me/918128840055" class="wa" style="color: #16a34a; font-weight: 600;">WhatsApp</a>
                                </span>
                                <br><br>
                                <strong>Mobile:</strong> <a href="tel:+918128840055" style="color: #1F6AAE;">+91 8128840055</a><br>
                                <strong>Email:</strong> <a href="mailto:info@techstrota.com" style="color: #1F6AAE;">info@techstrota.com</a><br>
                                <strong>CIN:</strong> GJ240114897<br><br>
                                <strong>Location:</strong> <a href="https://maps.app.goo.gl/4mb5cuMVjggNEaNj7" style="color: #1F6AAE;">503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat, <b>India</b> – 390007</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
        </table>
    </div>
</body>
</html>
