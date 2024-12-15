<?php
session_start();
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

$username = $_SESSION['username'];

// If the form is submitted, handle the selfie saving
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_POST['selfie_data'])) {
        $selfie_data = $_POST['selfie_data'];

        // Remove the 'data:image/png;base64,' part of the base64 data
        $selfie_data = str_replace('data:image/png;base64,', '', $selfie_data);
        $selfie_data = base64_decode($selfie_data);

        // Generate a unique file name for the selfie (with jpg extension)
        $selfie_filename = "selfie_" . time() . ".jpg";
        $selfie_path = "uploads/selfies/" . $selfie_filename;  // Saving in 'uploads/selfies/'

        // Ensure the selfies directory exists
        if (!is_dir('uploads/selfies')) {
            mkdir('uploads/selfies', 0777, true);
        }

        // Debugging: Check if the selfies directory is writable
        if (is_writable('uploads/selfies')) {
            echo "<p>Uploads/selfies folder is writable.</p>";
        } else {
            echo "<p>Uploads/selfies folder is not writable. Check the permissions.</p>";
        }

        // Save the image to the server as a JPG file
        $img = imagecreatefromstring($selfie_data);
        if ($img !== false) {
            if (imagejpeg($img, $selfie_path)) {
                imagedestroy($img);

                // Debugging: Check if the image was saved successfully
                echo "<p>Selfie saved to: " . $selfie_path . "</p>";

                // Update the user's selfie in the database with the username from the session
                $sql = "UPDATE users SET selfie='$selfie_path' WHERE username='$username'";

                if ($conn->query($sql) === TRUE) {
                    // Set a session variable to indicate success
                    $_SESSION['selfie_success'] = true;
                } else {
                    echo "Error uploading selfie: " . $conn->error;
                }
            } else {
                echo "Failed to save the image.";
                exit();
            }
        } else {
            echo "Failed to create image from data.";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture Selfie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        video {
            border: 1px solid #ccc;
            width: 100%;
            height: 100vh;
            object-fit: contain;
        }
        iframe {
            display: none;
        }
        canvas {
            display: none;
            width: 100%;
            height: 100vh;
            object-fit: contain;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            position: absolute;
            bottom: 90px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        #back-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        #success-message {
            display: none;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
        #timer {
            position: absolute;
            top: 50%;
            font-size: 48px;
            color: red;
            z-index: 100;
        }
    </style>
</head>
<body>

    <h2>Capture Your Selfie</h2>

    <video id="video" autoplay></video>
    <canvas id="canvas"></canvas>
    
    <form method="POST" id="selfie-form">
        <input type="hidden" name="selfie_data" id="selfie_data" />
        <button type="button" id="capture-button">Capture Selfie</button>
    </form>

    <!-- Back Button -->
    <button id="back-button" onclick="window.location.href='Home.html'">Back to Home</button>

    <div id="success-message"></div>

    <div id="timer"></div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const selfieDataInput = document.getElementById('selfie_data');
        const captureButton = document.getElementById('capture-button');
        const successMessage = document.getElementById('success-message');
        const timerElement = document.getElementById('timer');
        
        // Access the webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.log('Error accessing webcam: ', err);
            });

        // Countdown Timer before capturing
        let countdown = 3;
        let countdownInterval;

        captureButton.addEventListener('click', function(e) {
            e.preventDefault();  // Prevent the default button click behavior

            // Show countdown timer
            timerElement.textContent = countdown;
            countdownInterval = setInterval(function() {
                countdown--;
                timerElement.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    captureSelfie();
                }
            }, 1000);  // Update every second

        });

        function captureSelfie() {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Get the base64 image data from the canvas
            const selfieData = canvas.toDataURL('image/png');

            // Set the selfie data in the hidden input field
            selfieDataInput.value = selfieData;

            // Submit the form after the selfie is captured
            document.getElementById('selfie-form').submit(); // Explicitly use form ID
        }

        // Check if the PHP session has set the success flag
        window.onload = function() {
            <?php if (isset($_SESSION['selfie_success']) && $_SESSION['selfie_success'] === true) : ?>
                successMessage.textContent = "Image successfully saved!";
                successMessage.style.display = 'block';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                    <?php unset($_SESSION['selfie_success']); ?>  // Clear the session variable
                    window.location.href = 'Mainpage.php'; // Redirect to Mainpage.php
                }, 2000);
            <?php endif; ?>
        };
    </script>

</body>
</html>
