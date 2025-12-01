<?php
require_once 'database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $registrationNumber = $_POST['registration_number'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    
    // Check if registration number already exists
    $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM students WHERE registration_number = ?");
    $checkQuery->execute([$registrationNumber]);
    $count = $checkQuery->fetchColumn();
    
    if ($count > 0) {
        $message = "Student with registration number $registrationNumber already exists!";
        $messageType = "error";
    } else {
        // Create directory for student images
        $folderPath = "student_images/{$registrationNumber}/";
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        
        $imageFiles = [];
        
        // Process captured images
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_POST["captured_image_$i"])) {
                $base64Data = explode(',', $_POST["captured_image_$i"])[1];
                $imageData = base64_decode($base64Data);
                $fileName = "{$i}.png";
                file_put_contents("{$folderPath}{$fileName}", $imageData);
                $imageFiles[] = $fileName;
            }
        }
        
        if (count($imageFiles) >= 3) { // Require at least 3 images
            try {
                // Insert student data
                $insertQuery = $pdo->prepare("
                    INSERT INTO students (registration_number, first_name, last_name, email, images) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $insertQuery->execute([
                    $registrationNumber,
                    $firstName,
                    $lastName,
                    $email,
                    json_encode($imageFiles)
                ]);
                
                $message = "Student registered successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "Please capture at least 3 images!";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .image-section {
            margin: 30px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        
        .image-section h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .capture-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .capture-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .capture-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .images-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .image-box {
            position: relative;
            width: 120px;
            height: 120px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .nav-links {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .nav-links a {
            display: inline-block;
            margin: 0 15px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background: #764ba2;
        }
        
        .video-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .video-container video {
            width: 640px;
            height: 480px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Registration</h1>
            <p>Register students for face recognition attendance system</p>
        </div>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form method="post" id="registrationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="registration_number">Registration Number *</label>
                        <input type="text" id="registration_number" name="registration_number" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="image-section">
                    <h3>Student Photos (Minimum 3 required)</h3>
                    <button type="button" class="capture-btn" onclick="startCapture()">
                        Start Camera Capture
                    </button>
                    
                    <div class="images-container" id="imagesContainer">
                        <!-- Captured images will be displayed here -->
                    </div>
                </div>
                
                <button type="submit" name="register" class="submit-btn">Register Student</button>
            </form>
        </div>
        
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="mark_attendance.php">Mark Attendance</a>
        </div>
    </div>
    
    <div class="video-container" id="videoContainer">
        <video id="video" autoplay></video>
    </div>
    
    <script>
        let capturedImages = [];
        let currentCaptureIndex = 0;
        let videoStream = null;
        
        async function startCapture() {
            const videoContainer = document.getElementById('videoContainer');
            const video = document.getElementById('video');
            
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480 } 
                });
                video.srcObject = videoStream;
                videoContainer.style.display = 'flex';
                
                // Start capturing images automatically
                setTimeout(() => captureImage(), 1000);
                
            } catch (error) {
                alert('Unable to access camera. Please ensure camera permissions are granted.');
                console.error('Camera error:', error);
            }
        }
        
        function captureImage() {
            const video = document.getElementById('video');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0);
            
            const imageData = canvas.toDataURL('image/png');
            capturedImages.push(imageData);
            
            // Display captured image
            displayCapturedImage(imageData, capturedImages.length);
            
            currentCaptureIndex++;
            
            if (currentCaptureIndex < 5) {
                setTimeout(() => captureImage(), 1000);
            } else {
                // Stop camera and hide video container
                stopCamera();
            }
        }
        
        function displayCapturedImage(imageData, index) {
            const container = document.getElementById('imagesContainer');
            const imageBox = document.createElement('div');
            imageBox.className = 'image-box';
            
            const img = document.createElement('img');
            img.src = imageData;
            
            const numberLabel = document.createElement('div');
            numberLabel.className = 'image-number';
            numberLabel.textContent = `Image ${index}`;
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `captured_image_${index}`;
            hiddenInput.value = imageData;
            
            imageBox.appendChild(img);
            imageBox.appendChild(numberLabel);
            imageBox.appendChild(hiddenInput);
            
            container.appendChild(imageBox);
        }
        
        function stopCamera() {
            const video = document.getElementById('video');
            const videoContainer = document.getElementById('videoContainer');
            
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            videoContainer.style.display = 'none';
            currentCaptureIndex = 0;
        }
        
        // Stop camera when clicking outside video container
        document.getElementById('videoContainer').addEventListener('click', function(e) {
            if (e.target === this) {
                stopCamera();
            }
        });
    </script>
</body>
</html>