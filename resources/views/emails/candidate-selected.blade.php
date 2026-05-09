<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Congratulations - Selected</title>
</head>
<body style="font-family: Arial, sans-serif; font-size:14px; color:#333;">

    <h2 style="color:#28a745;">Congratulations! 🎉</h2>

    <p>Dear {{ $assignment->application->name ?? 'Candidate' }},</p>

    <p>
        We are pleased to inform you that you have been <strong>selected</strong> 
        for the internship program at <strong>TechStrota</strong>.
    </p>

    <p><strong>Batch:</strong> 
        {{ $assignment->batch->interview_batch_name ?? 'Not Assigned' }}
    </p>

    <p>
        <strong>Total Score:</strong>
        {{ ($assignment->problem_solving ?? 0) + ($assignment->communication ?? 0) }}/50
    </p>

    <p>
        Our team will contact you soon with further details regarding 
        joining formalities and documentation.
    </p>

    <hr style="border:none;border-top:1px solid #ccc;margin:20px 0;">

    <p style="color:#555;">
        We congratulate you on your performance and look forward to 
        working with you.
    </p>

    <p>Best Regards,<br>TechStrota Team</p>

    <!-- Signature Section -->
    <table cellpadding="0" cellspacing="0" border="0"
           style="font-family: Arial, sans-serif; font-size:14px; color:#333;">
        <tr>
            <!-- Logo -->
            <td style="vertical-align:middle; padding-right:10px;">
                <img 
                    src="https://techstrota.com/_next/image?url=%2Flogo.png&w=384&q=75"
                    alt="TechStrota Logo"
                    width="170"
                    style="display:block; max-width:170px; height:auto;"
                />
            </td>

            <!-- Company Details -->
            <td style="vertical-align:middle;">
                <strong style="font-size:16px;">
                    <a href="https://techstrota.com" style="text-decoration:none; color:#1F6AAE;">
                        TechStrota
                    </a>
                </strong><br>

                <a href="https://techstrota.com" style="color:#1F6AAE; text-decoration:underline;">Website</a> |
                <a href="https://linkedin.com/company/techstrota" style="color:#1F6AAE; text-decoration:underline;">LinkedIn</a> |
                <a href="https://wa.me/918128840055" style="color:green; text-decoration:underline;">WhatsApp</a>
                <br>

                Mobile:
                <a href="tel:+918128840055" style="color:#1F6AAE; text-decoration:underline;">
                    +91 8128840055
                </a>,
                Email:
                <a href="mailto:sales@techstrota.com" style="color:#1F6AAE; text-decoration:underline;">
                    sales@techstrota.com
                </a>
                <br>

                CIN: GJ240114897<br>
                503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat,
                <b>India</b> – 390007
            </td>
        </tr>
    </table>

</body>
</html>