# Face Attendance System Implementation Guide

## Overview
This guide provides the essential code snippets for implementing student registration and face-based attendance system. The system uses Face-API.js for face recognition and PHP for backend processing.

## Required Files Structure
```
your_project/
├── database/
│   └── connection.php
├── models/ (Face-API models)
│   ├── ssd_mobilenetv1_model-shard1
│   ├── ssd_mobilenetv1_model-shard2
│   ├── ssd_mobilenetv1_model-weights_manifest.json
│   ├── face_recognition_model-shard1
│   ├── face_recognition_model-shard2
│   ├── face_recognition_model-weights_manifest.json
│   ├── face_landmark_68_model-shard1
│   └── face_landmark_68_model-weights_manifest.json
├── student_images/
│   └── {registration_number}/
│       ├── 1.png
│       ├── 2.png
│       ├── 3.png
│       ├── 4.png
│       └── 5.png
├── student_registration.php
├── attendance_system.php
├── save_student.php
├── save_attendance.php
└── get_students.php
```

## Database Setup

### SQL for Student Table
```sql
CREATE TABLE `students` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `registration_number` varchar(50) NOT NULL UNIQUE,
  `student_images` text DEFAULT NULL,
  `date_registered` date NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `attendance` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_registration_number` varchar(50) NOT NULL,
  `attendance_status` varchar(20) NOT NULL DEFAULT 'Absent',
  `date_marked` date NOT NULL,
  `time_marked` time NOT NULL,
  PRIMARY KEY (`id`)
);
```

## 1. Student Registration Code

### Database Connection (database/connection.php)
```php
<?php
$host = "localhost";
$database = "your_database_name";
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### Student Registration Form (student_registration.php)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .image-capture {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .image-box {
            display: inline-block;
            margin: 10px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .image-box img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Student Registration</h1>
    
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message ' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>
    
    <form method="post" action="save_student.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="registration_number">Registration Number:</label>
            <input type="text" id="registration_number" name="registration_number" required>
        </div>
        
        <div class="image-capture">
            <h3>Capture Student Photos (5 images required)</h3>
            <button type="button" class="btn" onclick="captureImages()">Capture Images</button>
            <div id="image-container"></div>
        </div>
        
        <button type="submit" class="btn">Register Student</button>
    </form>

    <script>
        let capturedImages = [];
        
        function captureImages() {
            const imageContainer = document.getElementById('image-container');
            imageContainer.innerHTML = '';
            capturedImages = [];
            
            // Capture 5 images
            for (let i = 1; i <= 5; i++) {
                captureImage(i);
            }
        }
        
        function captureImage(imageNumber) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
                    
                    setTimeout(() => {
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0);
                        
                        const imageData = canvas.toDataURL('image/png');
                        capturedImages.push(imageData);
                        
                        // Display captured image
                        const imageBox = document.createElement('div');
                        imageBox.className = 'image-box';
                        
                        const img = document.createElement('img');
                        img.src = imageData;
                        img.alt = `Image ${imageNumber}`;
                        
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `image_${imageNumber}`;
                        hiddenInput.value = imageData;
                        
                        imageBox.appendChild(img);
                        imageBox.appendChild(hiddenInput);
                        
                        document.getElementById('image-container').appendChild(imageBox);
                        
                        // Stop video stream
                        stream.getTracks().forEach(track => track.stop());
                        document.body.removeChild(video);
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error accessing camera:', error);
                    alert('Unable to access camera. Please ensure camera permissions are granted.');
                });
        }
    </script>
</body>
</html>
```

### Save Student Processing (save_student.php)
```php
<?php
session_start();
require_once 'database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $registrationNumber = $_POST['registration_number'];
    $dateRegistered = date("Y-m-d");
    
    // Check if registration number already exists
    $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM students WHERE registration_number = :registration_number");
    $checkQuery->execute([':registration_number' => $registrationNumber]);
    $count = $checkQuery->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['message'] = "Student with registration number $registrationNumber already exists!";
        $_SESSION['message_type'] = "error";
        header("Location: student_registration.php");
        exit();
    }
    
    // Create directory for student images
    $folderPath = "student_images/{$registrationNumber}/";
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    
    $imageFileNames = [];
    
    // Process and save 5 images
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_POST["image_$i"])) {
            $base64Data = explode(',', $_POST["image_$i"])[1];
            $imageData = base64_decode($base64Data);
            $fileName = "{$i}.png";
            file_put_contents("{$folderPath}{$fileName}", $imageData);
            $imageFileNames[] = $fileName;
        }
    }
    
    // Convert image file names to JSON
    $imagesJson = json_encode($imageFileNames);
    
    try {
        // Insert student data
        $insertQuery = $pdo->prepare("
            INSERT INTO students 
            (first_name, last_name, email, registration_number, student_images, date_registered) 
            VALUES 
            (:first_name, :last_name, :email, :registration_number, :student_images, :date_registered)
        ");
        
        $insertQuery->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':registration_number' => $registrationNumber,
            ':student_images' => $imagesJson,
            ':date_registered' => $dateRegistered
        ]);
        
        $_SESSION['message'] = "Student registered successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: student_registration.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: student_registration.php");
        exit();
    }
}
?>
```

## 2. Face Attendance System Code

### Attendance System Interface (attendance_system.php)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Attendance System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .video-section {
            flex: 1;
        }
        .student-section {
            flex: 1;
        }
        .video-container {
            position: relative;
            margin-bottom: 20px;
        }
        #video {
            width: 100%;
            max-width: 600px;
            height: auto;
            border: 1px solid #ddd;
        }
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
        }
        .controls {
            margin: 20px 0;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .student-table th, .student-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .student-table th {
            background-color: #f2f2f2;
        }
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Face Attendance System</h1>
    
    <div id="message-container"></div>
    
    <div class="container">
        <div class="video-section">
            <div class="video-container">
                <video id="video" width="600" height="450" autoplay></video>
                <canvas id="overlay"></canvas>
            </div>
            
            <div class="controls">
                <button id="startButton" class="btn">Start Face Recognition</button>
                <button id="endButton" class="btn btn-danger" style="display: none;">End Attendance</button>
                <button id="saveButton" class="btn" style="display: none;">Save Attendance</button>
            </div>
        </div>
        
        <div class="student-section">
            <h2>Student Attendance</h2>
            <div id="student-table-container">
                <!-- Student table will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Include Face-API.js -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    
    <script>
        let students = [];
        let detectedFaces = [];
        let videoStream = null;
        let isRecognizing = false;

        // Load students and initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadStudents();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('startButton').addEventListener('click', startFaceRecognition);
            document.getElementById('endButton').addEventListener('click', endAttendance);
            document.getElementById('saveButton').addEventListener('click', saveAttendance);
        }

        async function loadStudents() {
            try {
                const response = await fetch('get_students.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    students = data.students;
                    displayStudentTable();
                } else {
                    showMessage('Error loading students: ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('Error loading students: ' + error.message, 'error');
            }
        }

        function displayStudentTable() {
            const container = document.getElementById('student-table-container');
            let html = `
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Registration No</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            students.forEach(student => {
                html += `
                    <tr id="student-${student.registration_number}">
                        <td>${student.registration_number}</td>
                        <td>${student.first_name} ${student.last_name}</td>
                        <td class="absent" id="status-${student.registration_number}">Absent</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = html;
        }

        async function startFaceRecognition() {
            try {
                // Load face recognition models
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri('models'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('models')
                ]);
                
                // Start webcam
                const video = document.getElementById('video');
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 600, height: 450 } 
                });
                video.srcObject = videoStream;
                
                // Wait for video to load
                await new Promise(resolve => {
                    video.onloadeddata = resolve;
                });
                
                // Load labeled face descriptions
                const labeledDescriptors = await loadLabeledFaceDescriptions();
                const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors);
                
                // Setup canvas for face detection overlay
                const canvas = faceapi.createCanvasFromMedia(video);
                const videoContainer = document.querySelector('.video-container');
                videoContainer.appendChild(canvas);
                
                const displaySize = { width: video.width, height: video.height };
                faceapi.matchDimensions(canvas, displaySize);
                
                // Start face recognition
                isRecognizing = true;
                document.getElementById('startButton').style.display = 'none';
                document.getElementById('endButton').style.display = 'inline-block';
                
                recognizeFaces(video, canvas, faceMatcher, displaySize);
                
            } catch (error) {
                showMessage('Error starting face recognition: ' + error.message, 'error');
            }
        }

        async function loadLabeledFaceDescriptions() {
            const labeledDescriptors = [];
            
            for (const student of students) {
                const descriptions = [];
                
                for (let i = 1; i <= 5; i++) {
                    try {
                        const img = await faceapi.fetchImage(`student_images/${student.registration_number}/${i}.png`);
                        const detection = await faceapi
                            .detectSingleFace(img)
                            .withFaceLandmarks()
                            .withFaceDescriptor();
                        
                        if (detection) {
                            descriptions.push(detection.descriptor);
                        }
                    } catch (error) {
                        console.log(`Error loading image ${student.registration_number}/${i}.png:`, error);
                    }
                }
                
                if (descriptions.length > 0) {
                    labeledDescriptors.push(
                        new faceapi.LabeledFaceDescriptors(student.registration_number, descriptions)
                    );
                }
            }
            
            return labeledDescriptors;
        }

        async function recognizeFaces(video, canvas, faceMatcher, displaySize) {
            if (!isRecognizing) return;
            
            const detections = await faceapi
                .detectAllFaces(video)
                .withFaceLandmarks()
                .withFaceDescriptors();
            
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            
            // Clear previous drawings
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw face detections and labels
            const results = resizedDetections.map(d => {
                return faceMatcher.findBestMatch(d.descriptor);
            });
            
            detectedFaces = results.map(result => result.label);
            updateAttendanceStatus(detectedFaces);
            
            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, {
                    label: result.toString()
                });
                drawBox.draw(canvas);
            });
            
            // Continue recognition
            setTimeout(() => recognizeFaces(video, canvas, faceMatcher, displaySize), 100);
        }

        function updateAttendanceStatus(detectedFaces) {
            students.forEach(student => {
                const statusElement = document.getElementById(`status-${student.registration_number}`);
                const rowElement = document.getElementById(`student-${student.registration_number}`);
                
                if (detectedFaces.includes(student.registration_number)) {
                    statusElement.textContent = 'Present';
                    statusElement.className = 'present';
                    rowElement.classList.add('present');
                } else {
                    statusElement.textContent = 'Absent';
                    statusElement.className = 'absent';
                    rowElement.classList.remove('present');
                }
            });
        }

        function endAttendance() {
            isRecognizing = false;
            
            // Stop webcam
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            // Remove canvas overlay
            const canvas = document.getElementById('overlay');
            if (canvas) {
                canvas.remove();
            }
            
            document.getElementById('endButton').style.display = 'none';
            document.getElementById('saveButton').style.display = 'inline-block';
            
            showMessage('Face recognition ended. Please save attendance.', 'success');
        }

        async function saveAttendance() {
            const attendanceData = [];
            
            students.forEach(student => {
                const statusElement = document.getElementById(`status-${student.registration_number}`);
                const status = statusElement.textContent;
                
                attendanceData.push({
                    registration_number: student.registration_number,
                    attendance_status: status,
                    date_marked: new Date().toISOString().split('T')[0],
                    time_marked: new Date().toTimeString().split(' ')[0]
                });
            });
            
            try {
                const response = await fetch('save_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(attendanceData)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showMessage('Attendance saved successfully!', 'success');
                    document.getElementById('saveButton').style.display = 'none';
                    document.getElementById('startButton').style.display = 'inline-block';
                } else {
                    showMessage('Error saving attendance: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Error saving attendance: ' + error.message, 'error');
            }
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
```

### Get Students API (get_students.php)
```php
<?php
header('Content-Type: application/json');
require_once 'database/connection.php';

try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY first_name, last_name");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'students' => $students
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
```

### Save Attendance Processing (save_attendance.php)
```php
<?php
header('Content-Type: application/json');
require_once 'database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);
    
    if ($attendanceData) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO attendance 
                (student_registration_number, attendance_status, date_marked, time_marked) 
                VALUES (:registration_number, :attendance_status, :date_marked, :time_marked)
            ");
            
            foreach ($attendanceData as $data) {
                $stmt->execute([
                    ':registration_number' => $data['registration_number'],
                    ':attendance_status' => $data['attendance_status'],
                    ':date_marked' => $data['date_marked'],
                    ':time_marked' => $data['time_marked']
                ]);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Attendance saved successfully'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error saving attendance: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No attendance data received'
        ]);
    }
}
?>
```

## Implementation Steps

### 1. Download Face-API Models
Download the required models from the Face-API.js repository and place them in the `models/` directory:
- ssd_mobilenetv1_model files
- face_recognition_model files  
- face_landmark_68_model files

### 2. Setup Database
Create the database tables using the provided SQL script.

### 3. Configure Database Connection
Update `database/connection.php` with your database credentials.

### 4. Implement Student Registration
- Use `student_registration.php` for the registration form
- Use `save_student.php` to process registration data
- Ensure `student_images/` directory is writable

### 5. Implement Face Attendance
- Use `attendance_system.php` for the attendance interface
- Use `get_students.php` to retrieve student data
- Use `save_attendance.php` to save attendance records

### 6. Test the System
1. Register a few students with their photos
2. Test the face recognition system
3. Verify attendance data is saved correctly

## Important Notes

1. **Camera Permissions**: Ensure users grant camera permissions
2. **Image Quality**: Good lighting and clear face images improve recognition accuracy
3. **Browser Compatibility**: Modern browsers with WebRTC support required
4. **Security**: Implement proper authentication and authorization
5. **Performance**: Consider server specifications for large numbers of students

## Troubleshooting

- **Face Recognition Not Working**: Check model files are correctly placed
- **Camera Access Issues**: Verify HTTPS is used in production
- **Image Storage Errors**: Check directory permissions
- **Database Errors**: Verify connection details and table structure