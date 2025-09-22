<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epic Cache Clear</title>
    <style>
        body {
            background: black;
            color: #0f0;
            font-family: "Courier New", monospace;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #output {
            white-space: pre;
            font-size: 16px;
            line-height: 1.3;
            margin-bottom: 20px;
        }

        .progress-container {
            width: 80%;
            background-color: #222;
            border: 2px solid #0f0;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #0f0;
            text-align: center;
            line-height: 20px;
            color: black;
            font-weight: bold;
            transition: width 0.1s linear;
        }
    </style>
</head>

<body>

    <pre id="output">
🚀 SYSTEM HACK INITIATED...
Clearing caches...
</pre>

    <div class="progress-container">
        <div class="progress-bar" id="progress-bar">0%</div>
    </div>

    <script>
        const progressBar = document.getElementById('progress-bar');
        let width = 0;

        // Progress bar animation
        const interval = setInterval(() => {
            if (width >= 100) {
                clearInterval(interval);
                document.getElementById('output').textContent += '\n✅ All caches cleared!';
            } else {
                width++;
                progressBar.style.width = width + '%';
                progressBar.textContent = width + '%';
            }
        }, 30); // 30ms * 100 = ~3 seconds
    </script>

</body>

</html>
