<!DOCTYPE html>
<html>
<head>
    <title>Your Media Received a Like</title>
</head>
<body>
    <h1>Your {{ $likeData['media_type'] }} Received a Like</h1>
    <p>Hello,</p>
    <p>{{ $likeData['liker_name'] }} liked your {{ $likeData['media_type'] }}.</p>
    <p>View your {{ $likeData['media_type'] }} here: <a href="{{ url('/media/' . $likeData['media_id']) }}">View {{ ucfirst($likeData['media_type']) }}</a></p>
</body>
</html>

