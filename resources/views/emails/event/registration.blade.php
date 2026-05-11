<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmed - {{ $events->first()->event_title ?? 'Events' }}</title>
</head>
<body style="font-family: Arial, sans-serif; font-size:14px; color:#333; line-height: 1.6;">

    <h2 style="color:#1F6AAE;">Registration Confirmed! 🎟️</h2>

    <p>Dear <strong>{{ $registration->name ?? 'Participant' }}</strong>,</p>
    <p>You have successfully registered for the following <strong>{{ count($events) }} event(s)</strong> hosted by <strong>TechStrota</strong>:</p>

    @foreach($events as $event)
        <div style="background-color: #f8f9fa; border-left: 4px solid #1F6AAE; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #e9ecef;">
            <h3 style="margin: 0 0 10px 0; color: #1F6AAE;">{{ $event->event_title }}</h3>
            
            <p style="margin: 5px 0; font-size: 13px;">
                <strong>📅 Date:</strong> 
                {{ \Carbon\Carbon::parse($event->event_start_date)->format('d M Y') }}
                @if($event->event_end_date && $event->event_end_date != $event->event_start_date)
                    - {{ \Carbon\Carbon::parse($event->event_end_date)->format('d M Y') }}
                @endif
            </p>

            {{-- logic based on 'type' column (online/offline) --}}
            <p style="margin: 5px 0; font-size: 13px;">
                <strong>📍 {{ strtolower($event->type) === 'online' ? 'Meeting Platform' : 'Venue' }}:</strong> 
                {{ $event->event_venue ?? 'To Be Announced' }}
            </p>

            @if($event->meeting_link)
                <p style="margin: 10px 0 0 0;">
                    <a href="{{ $event->meeting_link }}" style="display: inline-block; padding: 8px 16px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 12px;">
                        {{ strtolower($event->type) === 'online' ? '▶ Join Online Session' : '🗺️ View Directions' }}
                    </a>
                </p>
            @endif

            <p style="margin: 10px 0 0 0; font-size: 11px; color: #666; font-style: italic;">
                * @if(strtolower($event->type) === 'online')
                    We recommend joining 5-10 mins early to check your connection and audio/video settings.
                @else
                    We recommend reaching the venue 15 mins before the start time for on-site registration.
                @endif
            </p>
        </div>
    @endforeach

    <p style="margin-top: 20px;">Please save this email for your records. We look forward to seeing you!</p>

    <hr style="border:none; border-top:1px solid #ccc; margin:20px 0;">

    <p style="color:#555;">
        If you have any questions regarding the schedule or logistics, feel free to reach out to our support team via WhatsApp or Email.
    </p>

    <p>Best Regards,<br><strong>TechStrota Team</strong></p>

    <table cellpadding="0" cellspacing="0" border="0" style="font-family: Arial, sans-serif; font-size:12px; color:#333; margin-top: 30px;">
        <tr>
            <td style="vertical-align:middle; padding-right:15px;">
                <img 
                    src="https://techstrota.com/_next/image?url=%2Flogo.png&w=384&q=75"
                    alt="TechStrota Logo"
                    width="140"
                    style="display:block; max-width:140px; height:auto;"
                />
            </td>
            <td style="vertical-align:middle; border-left: 1px solid #ccc; padding-left: 15px;">
                <strong style="font-size:14px; color:#1F6AAE;">TechStrota</strong><br>
                <a href="https://techstrota.com" style="color:#1F6AAE; text-decoration:none;">Website</a> |
                <a href="https://linkedin.com/company/techstrota" style="color:#1F6AAE; text-decoration:none;">LinkedIn</a> |
                <a href="https://wa.me/918128840055" style="color:green; text-decoration:none;">WhatsApp</a>
                <br>
                <span style="color: #777;">
                    503, Sterling Centre, R C Dutt Road, Alkapuri, Vadodara, Gujarat - 390007
                </span>
            </td>
        </tr>
    </table>

</body>
</html>