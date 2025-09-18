<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Role Verification</title>
</head>

<body>
    <p>Hello {{ $name }},</p>

    <p>Thanks for signing up with us as a {{ $role }}.</p>

    @if ($role === 'Artist')
        <p>Please email <a href="mailto:thegulfcoastmusic@gmail.com">thegulfcoastmusic@gmail.com</a> to request
            verification as a Gulf Coast Artist.</p>
    @elseif($role === 'Venue')
        <p>Please email <a href="mailto:thegulfcoastmusic@gmail.com">thegulfcoastmusic@gmail.com</a> to request
            verification as a Gulf Coast Venue.</p>
    @elseif($role === 'Journalist')
        <p>Please email <a href="mailto:thegulfcoastmusic@gmail.com">thegulfcoastmusic@gmail.com</a> to request
            verification as a Gulf Coast Journalist.</p>
    @endif

    <p>We look forward to having you on Gulf Coast Music!</p>

    <p>Best regards,<br>
        Gulf Coast Music Team</p>
</body>

</html>
