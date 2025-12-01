<?php
require_once 'database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $attendanceData = json_decode($_POST['attendance_data'], true);
    
    if ($attendanceData) {
        try {
            $dateMarked = date('Y-m-d');
            $timeMarked = date('H:i:s');
            
            $insertQuery = $pdo->prepare("
                INSERT INTO attendance (registration_number, attendance_status, date_marked, time_marked) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($attendanceData as $data) {
                $insertQuery->execute([
                    $data['registration_number'],
                    $data['status'],
                    $dateMarked,
                    $timeMarked
                ]);
            }
            
            $message = "Attendance saved successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error saving attendance: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get all students for attendance
$studentsQuery = $pdo->query("SELECT * FROM students ORDER BY first_name, last_name");
$students = $studentsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
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
            max-width: 1200px;
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
        
        .content {
            padding: 40px;
        }
        
        .controls {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .attendance-section {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }
        
        .video-section {
            flex: 1;
            min-width: 300px;
        }
        
        .student-section {
            flex: 1;
            min-width: 300px;
        }
        
        .video-container {
            position: relative;
            background: #1a1a1a;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 640px;
            margin: 0 auto;
        }
        
        #video {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            max-height: 480px;
        }
        
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            width: 100%;
            height: 100%;
        }
        
        .section-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .student-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .student-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e1e5e9;
            transition: all 0.3s ease;
        }
        
        .student-item:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .student-item.present {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        
        .student-info {
            flex: 1;
        }
        
        .student-name {
            font-weight: 600;
            color: #333;
        }
        
        .student-reg {
            font-size: 0.9em;
            color: #666;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-present {
            background: #28a745;
            color: white;
        }
        
        .status-absent {
            background: #dc3545;
            color: white;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
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
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px 25px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e1e5e9;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        /* Enhanced Face Recognition Overlay Styles */
        .face-overlay {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 10;
        }
        
        .face-box {
            position: absolute;
            border: 3px solid;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        
        .face-box.processing {
            border-color: #ffc107;
            background: rgba(255, 193, 7, 0.1);
            animation: pulse 1.5s infinite;
        }
        
        .face-box.recognized {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }
        
        .face-box.unknown {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }
        
        .face-label {
            position: absolute;
            top: -30px;
            left: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            transform: translateX(-50%);
            left: 50%;
            min-width: 100px;
            text-align: center;
        }
        
        .face-label.processing {
            background: rgba(255, 193, 7, 0.9);
        }
        
        .face-label.recognized {
            background: rgba(40, 167, 69, 0.9);
        }
        
        .face-label.unknown {
            background: rgba(220, 53, 69, 0.9);
        }
        
        .confidence-score {
            font-size: 10px;
            opacity: 0.8;
            display: block;
            margin-top: 2px;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .recognition-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 20;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            animation: blink 2s infinite;
        }
        
        .status-indicator.active {
            background: #28a745;
        }
        
        .status-indicator.processing {
            background: #ffc107;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Face Recognition Attendance</h1>
            <p>Mark attendance using facial recognition technology</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <div class="controls">
                <button id="startBtn" class="btn btn-primary">Start Face Recognition</button>
                <button id="stopBtn" class="btn btn-danger" style="display: none;">Stop Recognition</button>
                <button id="saveBtn" class="btn btn-success" style="display: none;">Save Attendance</button>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number" id="totalCount"><?= count($students) ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="presentCount">0</div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="absentCount"><?= count($students) ?></div>
                    <div class="stat-label">Absent</div>
                </div>
            </div>
            
            <div class="attendance-section">
                <div class="video-section">
                    <h2 class="section-title">Camera Feed</h2>
                    <div class="video-container">
                        <video id="video" autoplay playsinline></video>
                        <canvas id="overlay"></canvas>
                        <div class="face-overlay" id="faceOverlay"></div>
                        <div class="recognition-status" id="recognitionStatus">
                            <span class="status-indicator processing" id="statusIndicator"></span>
                            <span id="statusText">Initializing...</span>
                        </div>
                    </div>
                </div>
                
                <div class="student-section">
                    <h2 class="section-title">Student List</h2>
                    <div class="student-list" id="studentList">
                        <?php foreach ($students as $student): ?>
                            <div class="student-item" id="student-<?= $student['registration_number'] ?>">
                                <div class="student-info">
                                    <div class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                                    <div class="student-reg"><?= htmlspecialchars($student['registration_number']) ?></div>
                                </div>
                                <span class="status-badge status-absent" id="status-<?= $student['registration_number'] ?>">Absent</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="register_student.php">Register Student</a>
        </div>
    </div>
    
    <!-- Include Face-API.js -->
    <script src="../resources/assets/javascript/face_logics/face-api.min.js"></script>
    
    <script>
        let students = <?= json_encode($students) ?>;
        let recognizedStudents = new Set();
        let isRecognizing = false;
        let videoStream = null;
        let faceDetectionInterval = null;
        
        // DOM elements
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const faceOverlay = document.getElementById('faceOverlay');
        const recognitionStatus = document.getElementById('recognitionStatus');
        const statusIndicator = document.getElementById('statusIndicator');
        const statusText = document.getElementById('statusText');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const saveBtn = document.getElementById('saveBtn');
        const presentCount = document.getElementById('presentCount');
        const absentCount = document.getElementById('absentCount');
        
        // Event listeners
        startBtn.addEventListener('click', startFaceRecognition);
        stopBtn.addEventListener('click', stopFaceRecognition);
        saveBtn.addEventListener('click', saveAttendance);
        
        // Update recognition status display
        function updateRecognitionStatus(status, text) {
            statusIndicator.className = `status-indicator ${status}`;
            statusText.textContent = text;
        }
        
        // Create face overlay element
        function createFaceOverlay(detection, result) {
            const box = detection.detection.box;
            const videoRect = video.getBoundingClientRect();
            const containerRect = document.querySelector('.video-container').getBoundingClientRect();
            
            // Calculate the correct position relative to the video container
            const scaleX = videoRect.width / video.videoWidth;
            const scaleY = videoRect.height / video.videoHeight;
            
            const faceBox = document.createElement('div');
            faceBox.className = 'face-box';
            
            // Position the face box correctly over the video
            faceBox.style.left = `${box.x * scaleX}px`;
            faceBox.style.top = `${box.y * scaleY}px`;
            faceBox.style.width = `${box.width * scaleX}px`;
            faceBox.style.height = `${box.height * scaleY}px`;
            
            const label = document.createElement('div');
            label.className = 'face-label';
            
            if (result && result.distance < 0.6) {
                faceBox.classList.add('recognized');
                label.classList.add('recognized');
                
                // Find student name
                const student = students.find(s => s.registration_number === result.label);
                const studentName = student ? `${student.first_name} ${student.last_name}` : result.label;
                
                label.innerHTML = `
                    ${studentName}
                    <span class="confidence-score">${Math.round((1 - result.distance) * 100)}% match</span>
                `;
                
                // Update student attendance
                updateStudentAttendance(result.label, 'Present');
            } else {
                faceBox.classList.add('unknown');
                label.classList.add('unknown');
                label.textContent = result ? 'Unknown Face' : 'Processing...';
            }
            
            faceBox.appendChild(label);
            return faceBox;
        }
        
        // Clear all face overlays
        function clearFaceOverlays() {
            faceOverlay.innerHTML = '';
        }
        
        // Update student attendance with visual feedback
        function updateStudentAttendance(registrationNumber, status) {
            if (status === 'Present') {
                recognizedStudents.add(registrationNumber);
                
                const studentElement = document.getElementById(`student-${registrationNumber}`);
                const statusElement = document.getElementById(`status-${registrationNumber}`);
                
                if (studentElement && statusElement) {
                    studentElement.classList.add('present');
                    statusElement.textContent = 'Present';
                    statusElement.className = 'status-badge status-present';
                    
                    // Add animation effect
                    studentElement.style.animation = 'pulse 0.5s ease-in-out';
                    setTimeout(() => {
                        studentElement.style.animation = '';
                    }, 500);
                }
                
                updateStats();
            }
        }
        
        async function startFaceRecognition() {
            try {
                updateRecognitionStatus('processing', 'Loading models...');
                
                // Load models from local directory
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri('./models'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('./models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('./models')
                ]);
                
                updateRecognitionStatus('processing', 'Starting camera...');
                
                // Start camera with optimal settings
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user'
                    }
                });
                video.srcObject = videoStream;
                
                // Wait for video to load and metadata
                await new Promise(resolve => {
                    video.onloadedmetadata = () => {
                        video.play();
                        resolve();
                    };
                });
                
                updateRecognitionStatus('processing', 'Loading face descriptors...');
                
                // Load face descriptors
                const labeledDescriptors = await loadFaceDescriptors();
                if (labeledDescriptors.length === 0) {
                    updateRecognitionStatus('', 'No student images found');
                    alert('No student images found. Please register students first.');
                    return;
                }
                
                const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors);
                
                // Setup canvas to match actual video dimensions
                const canvas = faceapi.createCanvasFromMedia(video);
                const videoContainer = document.querySelector('.video-container');
                videoContainer.appendChild(canvas);
                
                const displaySize = { 
                    width: video.videoWidth, 
                    height: video.videoHeight 
                };
                faceapi.matchDimensions(canvas, displaySize);
                
                // Style canvas to overlay properly
                canvas.style.position = 'absolute';
                canvas.style.top = '0';
                canvas.style.left = '0';
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                
                // Start recognition
                isRecognizing = true;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
                
                updateRecognitionStatus('active', 'Face Recognition Active');
                
                recognizeFaces(canvas, faceMatcher, displaySize);
                
            } catch (error) {
                console.error('Error starting face recognition:', error);
                updateRecognitionStatus('', 'Error: ' + error.message);
                alert('Error starting face recognition: ' + error.message);
            }
        }
        
        async function loadFaceDescriptors() {
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
        
        async function recognizeFaces(canvas, faceMatcher, displaySize) {
            if (!isRecognizing) return;
            
            try {
                const detections = await faceapi
                    .detectAllFaces(video)
                    .withFaceLandmarks()
                    .withFaceDescriptors();
                
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                
                // Clear canvas and overlays
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                clearFaceOverlays();
                
                // Process detections
                const results = resizedDetections.map(d => {
                    return faceMatcher.findBestMatch(d.descriptor);
                });
                
                // Create enhanced overlays for each detected face
                resizedDetections.forEach((detection, i) => {
                    const result = results[i];
                    const faceOverlayElement = createFaceOverlay(detection, result);
                    faceOverlay.appendChild(faceOverlayElement);
                });
                
                // Update recognition status based on results
                const hasRecognitions = results.some(r => r && r.distance < 0.6);
                if (hasRecognitions) {
                    updateRecognitionStatus('active', 'Face Recognition Active');
                } else if (detections.length > 0) {
                    updateRecognitionStatus('processing', 'Processing faces...');
                }
                
            } catch (error) {
                console.error('Error in face recognition:', error);
                updateRecognitionStatus('', 'Recognition error');
            }
            
            // Continue recognition with slightly slower interval for better performance
            faceDetectionInterval = setTimeout(() => recognizeFaces(canvas, faceMatcher, displaySize), 150);
        }
        
        function updateStudentAttendance(registrationNumber, status) {
            if (status === 'Present') {
                recognizedStudents.add(registrationNumber);
                
                const studentElement = document.getElementById(`student-${registrationNumber}`);
                const statusElement = document.getElementById(`status-${registrationNumber}`);
                
                if (studentElement && statusElement) {
                    studentElement.classList.add('present');
                    statusElement.textContent = 'Present';
                    statusElement.className = 'status-badge status-present';
                    
                    // Add animation effect
                    studentElement.style.animation = 'pulse 0.5s ease-in-out';
                    setTimeout(() => {
                        studentElement.style.animation = '';
                    }, 500);
                }
                
                updateStats();
            }
        }
        
        function updateStats() {
            const present = recognizedStudents.size;
            const total = students.length;
            const absent = total - present;
            
            presentCount.textContent = present;
            absentCount.textContent = absent;
        }
        
        function stopFaceRecognition() {
            isRecognizing = false;
            
            // Clear face detection interval
            if (faceDetectionInterval) {
                clearTimeout(faceDetectionInterval);
                faceDetectionInterval = null;
            }
            
            // Stop camera
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            // Clear overlays
            clearFaceOverlays();
            
            // Remove canvas
            const canvas = document.querySelector('canvas');
            if (canvas) {
                canvas.remove();
            }
            
            // Update status
            updateRecognitionStatus('', 'Recognition stopped');
            
            // Update buttons
            stopBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        }
        
        function saveAttendance() {
            const attendanceData = [];
            
            students.forEach(student => {
                const status = recognizedStudents.has(student.registration_number) ? 'Present' : 'Absent';
                attendanceData.push({
                    registration_number: student.registration_number,
                    status: status
                });
            });
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'mark_attendance.php';
            
            const attendanceInput = document.createElement('input');
            attendanceInput.type = 'hidden';
            attendanceInput.name = 'attendance_data';
            attendanceInput.value = JSON.stringify(attendanceData);
            
            const saveInput = document.createElement('input');
            saveInput.type = 'hidden';
            saveInput.name = 'save_attendance';
            saveInput.value = '1';
            
            form.appendChild(attendanceInput);
            form.appendChild(saveInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>