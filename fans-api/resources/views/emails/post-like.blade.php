<!DOCTYPE html>
<html>
<head>
    <title>Your Post Received a Like</title>
</head>
<body>
    <h1>Your Post Received a Like</h1>
    <p>Hello,</p>
    <p>{{ $likeData['liker_name'] }} liked your post.</p>
    <p>View your post here: <a href="{{ url('/posts/' . $likeData['post_id']) }}">View Post</a></p>
</body>
</html>

