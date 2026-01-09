<!DOCTYPE html>
<html>
<head>
    <title>New Message</title>
</head>
<body>
    <h1>You have a new message</h1>
    <p>From: {{ $messageData['sender_name'] }}</p>
    <p>Message: {{ $messageData['message_preview'] }}</p>
    <a href="{{ $messageData['message_link'] }}">View Message</a>
</body>
</html>

