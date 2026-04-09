<?php
// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/data';
$uploadsDir = __DIR__ . '/uploads';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoPath = '';
    $resumePath = '';
    
    // Handle Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoFile = $_FILES['photo'];
        $photoExt = pathinfo($photoFile['name'], PATHINFO_EXTENSION);
        $allowedPhotoExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($photoExt), $allowedPhotoExts) && $photoFile['size'] <= 5 * 1024 * 1024) {
            $photoName = 'photo_' . time() . '_' . uniqid() . '.' . $photoExt;
            $photoFullPath = $uploadsDir . '/' . $photoName;
            
            if (move_uploaded_file($photoFile['tmp_name'], $photoFullPath)) {
                $photoPath = 'uploads/' . $photoName;
            }
        }
    }
    
    // Handle Resume Upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $resumeFile = $_FILES['resume'];
        $resumeExt = pathinfo($resumeFile['name'], PATHINFO_EXTENSION);
        $allowedResumeExts = ['pdf', 'doc', 'docx', 'txt'];
        
        if (in_array(strtolower($resumeExt), $allowedResumeExts) && $resumeFile['size'] <= 10 * 1024 * 1024) {
            $resumeName = 'resume_' . time() . '_' . uniqid() . '.' . $resumeExt;
            $resumeFullPath = $uploadsDir . '/' . $resumeName;
            
            if (move_uploaded_file($resumeFile['tmp_name'], $resumeFullPath)) {
                $resumePath = 'uploads/' . $resumeName;
            }
        }
    }
    
    // Get form data
    $formData = [
        'username' => htmlspecialchars($_POST['username'] ?? ''),
        'email' => htmlspecialchars($_POST['email'] ?? ''),
        'usn' => htmlspecialchars($_POST['usn'] ?? ''),
        'gender' => htmlspecialchars($_POST['gender'] ?? ''),
        'languages' => $_POST['languages'] ?? [],
        'dob' => htmlspecialchars($_POST['dob'] ?? ''),
        'description' => htmlspecialchars($_POST['description'] ?? ''),
        'photo_path' => $photoPath,
        'resume_path' => $resumePath,
        'timestamp' => time()
    ];

    // Sanitize languages array
    $formData['languages'] = array_map('htmlspecialchars', $formData['languages']);

    // Create a unique filename based on timestamp
    $filename = $dataDir . '/form_' . $formData['timestamp'] . '_' . uniqid() . '.json';

    // Save the form data as JSON
    if (file_put_contents($filename, json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        // Redirect back to index with success message
        header('Location: index.php?success=1');
        exit();
    } else {
        // Handle error
        header('Location: index.php?error=1');
        exit();
    }
} else {
    // If not POST request, redirect to main form
    header('Location: index.php');
    exit();
}
