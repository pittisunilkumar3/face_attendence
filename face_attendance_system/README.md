# Face Attendance System

A complete face recognition-based student attendance system built with PHP, MySQL, and Face-API.js.

## Features

- **Student Registration**: Register students with facial data capture
- **Face Recognition**: Automatic attendance marking using facial recognition
- **Real-time Updates**: Live face detection and student status updates
- **Enhanced Visual Feedback**: Color-coded face recognition directly on camera feed
- **Database Storage**: Complete attendance record management
- **Responsive Design**: Works on desktop and mobile devices

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser with camera support
- HTTPS required for camera access in production

## Installation

### 1. Database Setup

Create a MySQL database named `face_attendance` and import the following SQL:

```sql
CREATE DATABASE IF NOT EXISTS face_attendance;
USE face_attendance;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    images JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(50) NOT NULL,
    attendance_status ENUM('Present', 'Absent') NOT NULL,
    date_marked DATE NOT NULL,
    time_marked TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_number) REFERENCES students(registration_number)
);
```

### 2. File Setup

1. Copy all files to your web server directory
2. Ensure the `student_images/` directory is writable by the web server
3. Update database connection in `database.php` if needed

### 3. Permissions

Set appropriate permissions:
```bash
chmod 755 student_images/
chmod 644 *.php *.html
```

## Usage

### 1. Register Students

1. Open `index.html` in your browser
2. Click "Register Student"
3. Fill in student details
4. Click "Start Camera Capture" to take 5 photos
5. Click "Register Student" to save

### 2. Mark Attendance

1. Click "Mark Attendance" from the main page
2. Click "Start Face Recognition"
3. Allow camera access when prompted
4. The system will automatically detect faces and mark attendance
5. Click "Stop Recognition" when done
6. Click "Save Attendance" to save records

## File Structure

```
face_attendance_system/
├── index.html              # Main landing page
├── register_student.php     # Student registration form
├── mark_attendance.php     # Face recognition attendance
├── database.php            # Database connection and setup
├── test_models.html        # Test page for local models
├── models/                 # Local face-api.js models (all 18 files)
│   ├── ssd_mobilenetv1_model*
│   ├── face_recognition_model*
│   ├── face_landmark_68_model*
│   ├── tiny_face_detector_model*
│   ├── face_landmark_68_tiny_model*
│   ├── mtcnn_model*
│   ├── face_expression_model*
│   └── age_gender_model*
├── student_images/         # Stored student face images
│   └── {reg_number}/      # Individual student folders
│       ├── 1.png
│       ├── 2.png
│       ├── 3.png
│       ├── 4.png
│       └── 5.png
└── README.md              # This file
```

## Enhanced Features

### Real-time Face Recognition Feedback
- **Color-coded Visual Indicators**:
  - 🟢 **Green**: Successfully recognized student with confidence score
  - 🟡 **Yellow**: Processing/analyzing face
  - 🔴 **Red**: Unknown face or failed recognition

- **Direct Camera Overlay**:
  - Face bounding boxes appear directly on video feed
  - Student names displayed above recognized faces
  - Confidence percentages shown for each match
  - Smooth animations and transitions

- **Status Indicators**:
  - Live status display showing current system state
  - Animated indicators for processing, active, and error states
  - Real-time feedback during all operations

### Enhanced User Experience
- **Immediate Visual Confirmation**: See faces being recognized in real-time
- **Confidence Scores**: Display match accuracy percentage
- **Animation Effects**: Pulse animations when students are marked present
- **Professional Interface**: Modern, responsive design with smooth transitions

## How It Works

### Student Registration
1. Student information is entered into a form
2. Camera captures 5 face images
3. Images are stored in `student_images/{registration_number}/`
4. Student data is saved to the database

### Enhanced Face Recognition Attendance
1. Face-API.js models are loaded from local directory (models/) for better performance and offline capability
2. Student face images are processed to create face descriptors
3. Camera feed is analyzed in real-time with enhanced overlay
4. Detected faces are matched against registered students
5. Visual feedback provided directly on video feed with accurate positioning:
   - Green boxes for recognized students (positioned correctly on face)
   - Yellow boxes for processing faces
   - Red boxes for unknown faces
   - Name labels with confidence scores
6. Attendance status updated automatically with animation effects
7. Results are saved to the database

## Technical Details

### Face Recognition Technology
- Uses Face-API.js library for face detection and recognition
- Implements SSD MobileNet v1 for face detection
- Uses face landmarks and descriptors for accurate matching
- Configurable distance threshold for recognition accuracy

### Database Schema
- **students**: Stores student information and image metadata
- **attendance**: Stores attendance records with timestamps
- Foreign key relationship ensures data integrity

### Security Considerations
- Input validation and sanitization
- Prepared statements for SQL injection prevention
- Session-based authentication (can be extended)
- HTTPS recommended for production

## Troubleshooting

### Camera Not Working
- Ensure browser permissions are granted
- Use HTTPS in production environments
- Check browser compatibility (Chrome, Firefox, Safari, Edge)

### Face Recognition Not Accurate
- Ensure good lighting conditions
- Capture clear, frontal face images during registration
- Adjust recognition threshold in the code if needed

### Database Connection Issues
- Verify database credentials in `database.php`
- Ensure MySQL server is running
- Check database and table creation

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## License

This project is open source and available under the MIT License.

## Support

For issues and questions, please check the troubleshooting section or create an issue in the project repository.