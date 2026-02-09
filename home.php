<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Snake Bite Game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #a0afed 0%, #174cd3 100%);
            font-family: Arial, sans-serif;
        }

        .game-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            text-align: center;
            position: relative;
        }

        h1 {
            margin: 10px 0;
            color: #333;
        }

        canvas {
            background: #111;
            border-radius: 6px;
            display: block;
            margin: 15px auto;
        }

        .score {
            font-size: 18px;
            color: #555;
        }

        .hint {
            font-size: 14px;
            color: #777;
        }

        .game-over {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.8);
            color: #fff;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .game-over h2 {
            margin-bottom: 10px;
        }

        .restart {
            padding: 8px 16px;
            background: #4CAF50;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="game-container">
    <h1>🐍 Snake Bite Game</h1>
    <div class="score">Score: <span id="score">0</span></div>

    <canvas id="game" width="300" height="300"></canvas>

    <div class="hint">Use ⬅️ ⬆️ ➡️ ⬇️ arrow keys</div>

    <!-- Game Over Screen -->
    <div class="game-over" id="gameOver">
        <h2>Game Over</h2>
        <p>Your Score: <span id="finalScore">0</span></p>
        <button class="restart" onclick="restartGame()">Restart</button>
    </div>
</div>

<script>
    const canvas = document.getElementById("game");
    const ctx = canvas.getContext("2d");

    const box = 15;
    const canvasSize = 300;

    let snake, direction, food, score, game;

    function initGame() {
        snake = [{ x: 150, y: 150 }];
        direction = "RIGHT";
        score = 0;
        food = generateFood();

        document.getElementById("score").textContent = score;
        document.getElementById("gameOver").style.display = "none";

        game = setInterval(drawGame, 120);
    }

    document.addEventListener("keydown", changeDirection);

    function changeDirection(event) {
        if (event.key === "ArrowLeft" && direction !== "RIGHT") direction = "LEFT";
        if (event.key === "ArrowUp" && direction !== "DOWN") direction = "UP";
        if (event.key === "ArrowRight" && direction !== "LEFT") direction = "RIGHT";
        if (event.key === "ArrowDown" && direction !== "UP") direction = "DOWN";
    }

    function generateFood() {
        return {
            x: Math.floor(Math.random() * (canvasSize / box)) * box,
            y: Math.floor(Math.random() * (canvasSize / box)) * box
        };
    }

    function collision(head, body) {
        return body.some(segment => segment.x === head.x && segment.y === head.y);
    }

    function drawGame() {
        ctx.clearRect(0, 0, canvasSize, canvasSize);

        // Draw snake
        snake.forEach((segment, index) => {
            ctx.fillStyle = index === 0 ? "lime" : "green";
            ctx.fillRect(segment.x, segment.y, box, box);
        });

        // Draw food
        ctx.fillStyle = "red";
        ctx.fillRect(food.x, food.y, box, box);

        let headX = snake[0].x;
        let headY = snake[0].y;

        if (direction === "LEFT") headX -= box;
        if (direction === "UP") headY -= box;
        if (direction === "RIGHT") headX += box;
        if (direction === "DOWN") headY += box;

        // Eat food
        if (headX === food.x && headY === food.y) {
            score++;
            document.getElementById("score").textContent = score;
            food = generateFood();
        } else {
            snake.pop();
        }

        let newHead = { x: headX, y: headY };

        // Game Over
        if (
            headX < 0 ||
            headY < 0 ||
            headX >= canvasSize ||
            headY >= canvasSize ||
            collision(newHead, snake)
        ) {
            clearInterval(game);
            document.getElementById("finalScore").textContent = score;
            document.getElementById("gameOver").style.display = "flex";
            return;
        }

        snake.unshift(newHead);
    }

    function restartGame() {
        clearInterval(game);
        initGame();
    }

    initGame();
</script>

</body>
</html>
