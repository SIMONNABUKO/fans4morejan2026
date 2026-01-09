<!DOCTYPE html>
<html>
<head>
    <title>You Have a New Follower</title>
</head>
<body>
    <h1>You Have a New Follower</h1>
    <p>Hello,</p>
    <p>{{ $followerData['follower_name'] }} is now following you.</p>
    <p>View their profile here: <a href="{{ url('/users/' . $followerData['follower_id']) }}">View Profile</a></p>
</body>
</html>

