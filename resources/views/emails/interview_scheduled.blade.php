<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Scheduled</title>
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
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 0.5px; }
        .content { padding: 40px; }
        .details-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin: 25px 0; }
        .details-card p { margin: 0 0 12px; font-size: 15px; color: #334155; }
        .details-card p:last-child { margin-bottom: 0; }
        .details-card strong { color: #0f172a; display: inline-block; width: 85px; }
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
            .details-card { background-color: #0f172a !important; border-color: #334155 !important; }
            .details-card p { color: #e2e8f0 !important; }
            .details-card strong { color: #f8fafc !important; }
            .footer { background-color: #0f172a !important; border-color: #334155 !important; }
            .sig-text-td { color: #94a3b8 !important; }
            .sig-text-td strong { color: #f8fafc !important; }
        }
        
        /* Mobile Styles */
        @media screen and (max-width: 600px) {
            .wrapper { padding: 15px !important; }
            .main { border-radius: 12px !important; width: 100% !important; max-width: 100% !important; }
            .header { padding: 25px 20px !important; }
            .content { padding: 30px 20px !important; }
            .footer { padding: 30px 20px !important; }
            
            /* Stack signature */
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
                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                        {{ !empty($isRescheduled) ? 'Interview Rescheduled' : 'Interview Scheduled' }}
                    </h1>
                </td>
            </tr>
            
            <!-- Content -->
            <tr>
                <td class="content" style="padding: 40px;">
                    <p style="margin-top: 0;">Dear <strong>{{ $application->name ?? 'Candidate' }}</strong>,</p>
                    <p>
                        @if(!empty($isRescheduled))
                            Your interview at <strong>TechStrota</strong> has been <strong>rescheduled</strong>. Please find your revised interview schedule details below:
                        @else
                            Congratulations! Your interview at <strong>TechStrota</strong> has been successfully scheduled.
                        @endif
                    </p>
                    
                    <!-- Details Card -->
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                        <tr>
                            <td class="details-card" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px;">
                                <p style="margin-top: 0;"><strong>Batch:</strong> {{ $batch->interview_batch_name ?? 'Not Assigned' }}</p>
                                <p><strong>Date:</strong> {{ $batch->interview_date ? \Carbon\Carbon::parse($batch->interview_date)->format('d-m-Y') : 'Not Assigned' }}</p>
                                <p><strong>Time:</strong> {{ $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : 'Not Assigned' }}</p>
                                <p style="margin-bottom: 0;"><strong>Location:</strong> {{ $batch->interview_location ?? 'Not Assigned' }}</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p>Please ensure you are available at the location <strong>15 minutes before</strong> the scheduled time.</p>
                    <p>If you have any questions or need to reschedule, feel free to reply directly to this email or reach out to us via WhatsApp.</p>
                    
                    <p style="margin-top: 35px; margin-bottom: 0;">Best Regards,<br><strong style="color: #1F6AAE;">TechStrota Team</strong></p>
                </td>
            </tr>
            
            <!-- Footer Signature -->
            <tr>
                <td class="footer" style="padding: 30px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <table class="sig-container" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <!-- Logo (Left) -->
                            <td class="sig-logo-td" style="padding-right: 25px; width: 140px; vertical-align: top;">
                                <!-- Wrapped in white bg for Dark Mode compatibility -->
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
