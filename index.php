<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #a0afed 0%, #174cd3 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #333;
            margin: 0;
        }
        p {
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello, World 👋</h1>
        <p>PHP Server is running successfully</p>
        <p><?php echo "Current time: " . date('Y-m-d H:i:s', strtotime('+ 4 hours 30 minutes')); ?></p>
        <p>click <a href="./home.php">here</a> to visit home</p>
    </div>
</body>
</html>
