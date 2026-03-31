<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reminder</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f5; margin: 0; padding: 24px; }
        .card { background: #fff; border-radius: 8px; max-width: 520px; margin: 0 auto; padding: 32px; }
        .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111827; margin-bottom: 16px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .virtual { background: #dbeafe; color: #1d4ed8; }
        .on-site { background: #dcfce7; color: #15803d; }
        .footer { margin-top: 32px; font-size: 13px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <p style="font-size:15px;color:#374151;">Hi {{ $recipient->name }},</p>
        <p style="font-size:15px;color:#374151;">
            This is a reminder that the following event starts in
            <strong>{{ $event->reminder_minutes }} minute{{ $event->reminder_minutes !== 1 ? 's' : '' }}</strong>.
        </p>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">

        <div class="label">Event</div>
        <div class="value" style="font-size:20px;font-weight:600;">{{ $event->title }}</div>

        <div class="label">Type</div>
        <div class="value">
            <span class="badge {{ $event->type === 'virtual' ? 'virtual' : 'on-site' }}">
                {{ ucfirst($event->type) }}
            </span>
        </div>

        @if ($event->description)
            <div class="label">Description</div>
            <div class="value">{{ $event->description }}</div>
        @endif

        <div class="label">Starts at</div>
        <div class="value">
            {{ $event->starts_at->setTimezone($event->effectiveTimezone())->format('D, d M Y \a\t H:i') }}
            ({{ $event->effectiveTimezone() }})
        </div>

        <div class="label">Ends at</div>
        <div class="value">
            {{ $event->ends_at->setTimezone($event->effectiveTimezone())->format('D, d M Y \a\t H:i') }}
        </div>

        @if ($event->type === 'virtual' && $event->meeting_url)
            <div class="label">Join link</div>
            <div class="value">
                <a href="{{ $event->meeting_url }}" style="color:#4f46e5;">{{ $event->meeting_url }}</a>
            </div>
        @endif

        @if ($event->type === 'on-site' && $event->address)
            <div class="label">Location</div>
            <div class="value">{{ $event->address }}</div>
        @endif

        <div class="footer">You are receiving this email because you were invited to this event.</div>
    </div>
</body>
</html>
