<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title : $error }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font: 110%/1.5 system-ui, sans-serif;
            background: #131417;
            color: white;
            height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .main {
            max-width: 350px;
        }

        a {
            color: #56BBF9;
        }
    </style>
</head>
<body>
<div class="main">
    <h1>{{ $error }}</h1>
    <p>{{ $description }}</p>
    <ul>
        <li>
            <a href="/">Home</a>
        </li>
        <li>
            <a href="javascript:history.back()">Go Back</a>
        </li>
    </ul>
</div>
</body>
</html>
