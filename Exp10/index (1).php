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

// Get all saved forms
$savedForms = [];
if (is_dir($dataDir)) {
    $files = glob($dataDir . '/*.json');
    foreach ($files as $file) {
        $savedForms[] = json_decode(file_get_contents($file), true);
    }
    // Sort by timestamp in descending order
    usort($savedForms, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
}

$showFilled = isset($_GET['show']) && $_GET['show'] === 'filled';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Form Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(63, 81, 181, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            z-index: -1;
            filter: blur(40px);
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            left: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(156, 39, 176, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 10s ease-in-out infinite reverse;
            z-index: -1;
            filter: blur(40px);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(40px) translateX(30px); }
        }

        .container {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.35), 0 0 1px rgba(255, 255, 255, 0.5) inset;
            padding: 60px;
            width: 100%;
            max-width: 1000px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            animation: fadeIn 0.8s ease-out 0.2s both;
        }

        .header-icon {
            font-size: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #9c27b0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        h1 {
            color: #1a1a2e;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.8px;
            background: linear-gradient(90deg, #1a1a2e 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: #888;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-group {
            margin-bottom: 28px;
            animation: slideUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .form-group:nth-child(1) { animation-delay: 0.15s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.25s; }
        .form-group:nth-child(4) { animation-delay: 0.3s; }
        .form-group:nth-child(5) { animation-delay: 0.35s; }
        .form-group:nth-child(6) { animation-delay: 0.4s; }
        .form-group:nth-child(7) { animation-delay: 0.45s; }
        .form-group:nth-child(8) { animation-delay: 0.5s; }
        .form-group:nth-child(9) { animation-delay: 0.55s; }

        label {
            display: block;
            margin-bottom: 12px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        label i {
            color: #667eea;
            width: 16px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1a1a2e;
        }

        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        textarea::placeholder {
            color: #ccc;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12), 0 2px 8px rgba(102, 126, 234, 0.15);
        }

        input[type="text"]:focus ~ input-wrapper::after,
        input[type="email"]:focus ~ input-wrapper::after,
        input[type="date"]:focus ~ input-wrapper::after,
        textarea:focus ~ input-wrapper::after {
            transform: scaleX(1);
        }

        input[type="file"] {
            padding: 12px;
            cursor: pointer;
        }

        input[type="file"]::-webkit-file-upload-button {
            padding: 9px 18px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-right: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }

        textarea {
            resize: vertical;
            min-height: 130px;
            font-family: inherit;
            line-height: 1.6;
        }

        /* Form sections visual improvements */
        .form-group:nth-child(4) {
            padding-top: 15px;
            border-top: 2px solid #f5f5f5;
            margin-top: 10px;
        }

        .form-group:nth-child(6) {
            padding-top: 15px;
            border-top: 2px solid #f5f5f5;
            margin-top: 10px;
        }

        .radio-group,
        .checkbox-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .radio-item,
        .checkbox-item {
            display: flex;
            align-items: center;
            position: relative;
        }

        input[type="radio"],
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
            accent-color: #667eea;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input[type="radio"]:hover,
        input[type="checkbox"]:hover {
            transform: scale(1.15);
            filter: drop-shadow(0 0 4px rgba(102, 126, 234, 0.5));
        }

        .radio-item label,
        .checkbox-item label {
            margin: 0;
            font-weight: 500;
            color: #2c3e50;
            cursor: pointer;
            font-size: 14px;
            transition: color 0.3s;
        }

        .radio-item input[type="radio"]:checked + label,
        .checkbox-item input[type="checkbox"]:checked + label {
            color: #667eea;
            font-weight: 600;
        }

        .file-preview {
            margin-top: 10px;
            padding: 12px 14px;
            background: linear-gradient(135deg, #f0f4ff 0%, #f9f5ff 100%);
            border-radius: 10px;
            font-size: 13px;
            color: #667eea;
            border-left: 3px solid #667eea;
            font-weight: 500;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 45px;
        }

        button {
            padding: 16px 28px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }

        .btn-save:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.45);
        }

        .btn-save:active {
            transform: translateY(-1px) scale(1);
        }

        .btn-display {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .btn-display:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.35);
            border-color: #667eea;
        }

        .btn-display:active {
            transform: translateY(-1px) scale(1);
        }

        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }

        .btn-back:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.45);
        }

        .btn-back:active {
            transform: translateY(-1px) scale(1);
        }

        /* Filled Forms Styles */
        .filled-forms-container {
            display: none;
        }

        .filled-forms-container.show {
            display: block;
        }

        .form-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 28px;
            margin-bottom: 35px;
        }

        .form-card {
            border: none;
            border-radius: 16px;
            padding: 28px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #9c27b0 100%);
            transition: left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
            transition: width 0.4s ease;
        }

        .form-card:hover::before {
            left: 100%;
        }

        .form-card:hover::after {
            width: 100%;
        }

        .form-card:hover {
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.25);
            transform: translateY(-10px) scale(1.02);
        }

        .card-photo {
            width: 90px;
            height: 90px;
            border-radius: 14px;
            margin-bottom: 18px;
            object-fit: cover;
            border: 3px solid #667eea;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .form-card:hover .card-photo {
            border-color: #764ba2;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
        }

        .form-card h3 {
            color: #1a1a2e;
            margin-bottom: 14px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-card h3 i {
            color: #667eea;
        }

        .form-card p {
            color: #666;
            font-size: 13px;
            margin-bottom: 11px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .form-card p i {
            color: #667eea;
            width: 16px;
            text-align: center;
        }

        .form-card:hover p {
            color: #764ba2;
        }

        .form-details {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 45px;
            margin-bottom: 35px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .form-details-header {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }

        .detail-photo {
            width: 130px;
            height: 130px;
            border-radius: 14px;
            object-fit: cover;
            margin-right: 30px;
            border: 4px solid #667eea;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
            transition: all 0.3s;
        }

        .form-details-header:hover .detail-photo {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.35);
        }

        .detail-header-text h2 {
            color: #1a1a2e;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        .detail-header-text p {
            color: #888;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-header-text p i {
            color: #667eea;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .detail-item {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            color: #667eea;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            margin-right: 0;
            font-size: 14px;
        }

        .detail-value {
            color: #2c3e50;
            font-size: 15px;
            font-weight: 500;
            word-break: break-word;
            line-height: 1.6;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        .resume-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .resume-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .empty-message {
            text-align: center;
            padding: 80px 40px;
            color: #aaa;
            font-size: 18px;
            animation: fadeIn 0.6s ease-out;
        }

        .empty-icon {
            font-size: 70px;
            color: #e0e0e0;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .empty-message:hover .empty-icon {
            color: #667eea;
            transform: scale(1.1);
        }

        .success-message {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.35);
            animation: slideDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .success-message i {
            font-size: 20px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .container {
                padding: 45px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .form-list {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px;
            }

            .header {
                margin-bottom: 35px;
                padding-bottom: 20px;
            }

            h1 {
                font-size: 28px;
            }

            .header-icon {
                font-size: 48px;
            }

            .subtitle {
                font-size: 14px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-list {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-details-header {
                flex-direction: column;
                text-align: center;
            }

            .detail-photo {
                margin-right: 0;
                margin-bottom: 25px;
                width: 110px;
                height: 110px;
            }

            .form-details {
                padding: 30px;
            }

            .form-card {
                padding: 22px;
            }

            button {
                padding: 14px 24px;
                font-size: 13px;
            }

            input[type="text"],
            input[type="email"],
            input[type="date"],
            textarea,
            input[type="file"] {
                padding: 12px 16px;
                font-size: 16px;
            }

            label {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .form-group {
                margin-bottom: 22px;
            }

            h1 {
                font-size: 24px;
            }

            .header-icon {
                font-size: 44px;
            }

            .form-details {
                padding: 22px;
            }

            input[type="file"]::-webkit-file-upload-button {
                padding: 7px 14px;
                font-size: 11px;
            }

            .button-group {
                gap: 12px;
            }

            .radio-group,
            .checkbox-group {
                gap: 15px;
            }

            .empty-message {
                padding: 60px 20px;
            }

            .empty-icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Form Input Section -->
        <div id="formSection" style="<?php echo $showFilled ? 'display: none;' : ''; ?>">
            <div class="header">
                <div class="header-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1>Professional Profile Form</h1>
                <p class="subtitle">Complete your profile with all necessary information</p>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Form saved successfully! You can now view your profile.</span>
                </div>
            <?php endif; ?>

            <form method="POST" action="save_form.php" enctype="multipart/form-data">
                <!-- Username -->
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name *</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="John Doe" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address *</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" placeholder="john@example.com" required>
                    </div>
                </div>

                <!-- USN -->
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> USN/ID Number *</label>
                    <div class="input-wrapper">
                        <input type="text" name="usn" placeholder="24BTRXXXXX" required>
                    </div>
                </div>

                <!-- Gender -->
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Gender *</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="male" name="gender" value="Male" required>
                            <label for="male">Male</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="female" name="gender" value="Female">
                            <label for="female">Female</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="other" name="gender" value="Other">
                            <label for="other">Other</label>
                        </div>
                    </div>
                </div>

                <!-- Languages -->
                <div class="form-group">
                    <label><i class="fas fa-code"></i> Technical Skills *</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="html" name="languages[]" value="HTML">
                            <label for="html">HTML</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="css" name="languages[]" value="CSS">
                            <label for="css">CSS</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="js" name="languages[]" value="JavaScript">
                            <label for="js">JavaScript</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="java" name="languages[]" value="Java">
                            <label for="java">Java</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="python" name="languages[]" value="Python">
                            <label for="python">Python</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="rust" name="languages[]" value="Rust">
                            <label for="rust">Rust</label>
                        </div>
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="form-group">
                    <label><i class="fas fa-birthday-cake"></i> Date of Birth *</label>
                    <div class="input-wrapper">
                        <input type="date" name="dob" required>
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="form-group">
                    <label><i class="fas fa-camera"></i> Profile Photo *</label>
                    <div class="input-wrapper">
                        <input type="file" name="photo" accept="image/*" required id="photoInput">
                        <div class="file-preview" id="photoPreview"></div>
                    </div>
                </div>

                <!-- Resume Upload -->
                <div class="form-group">
                    <label><i class="fas fa-file-pdf"></i> Resume/CV *</label>
                    <div class="input-wrapper">
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" required id="resumeInput">
                        <div class="file-preview" id="resumePreview"></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label><i class="fas fa-pen-fancy"></i> About Yourself *</label>
                    <textarea name="description" placeholder="Tell us about yourself, your experience, and career goals..." required></textarea>
                </div>

                <!-- Buttons -->
                <div class="button-group">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-check"></i> Save Profile
                    </button>
                    <button type="button" class="btn-display" onclick="displayFilledForms()">
                        <i class="fas fa-folder-open"></i> View Profiles
                    </button>
                </div>
            </form>
        </div>

        <!-- Filled Forms Display Section -->
        <div id="filledFormsSection" class="filled-forms-container" style="<?php echo $showFilled ? 'display: block;' : 'display: none;'; ?>">
            <div class="header">
                <div class="header-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h1>Saved Profiles</h1>
                <p class="subtitle">View and manage all your saved profiles</p>
            </div>

            <?php if (empty($savedForms)): ?>
                <div class="empty-message">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p>No profiles saved yet. Create one to get started!</p>
                </div>
            <?php else: ?>
                <div class="form-list">
                    <?php foreach ($savedForms as $index => $form): ?>
                        <div class="form-card" onclick="viewForm(<?php echo $index; ?>)">
                            <?php if (!empty($form['photo_path']) && file_exists($form['photo_path'])): ?>
                                <img src="<?php echo htmlspecialchars($form['photo_path']); ?>" alt="Profile" class="card-photo">
                            <?php endif; ?>
                            <h3>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($form['username']); ?>
                            </h3>
                            <p>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($form['email']); ?>
                            </p>
                            <p>
                                <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($form['usn']); ?>
                            </p>
                            <p>
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', $form['timestamp']); ?>
                            </p>
                            <p style="color: #667eea; margin-top: 18px; cursor: pointer; font-weight: 700; transition: all 0.3s;">
                                <i class="fas fa-arrow-right"></i> View Full Profile
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="button" class="btn-back" onclick="backToForm()">
                <i class="fas fa-arrow-left"></i> Back to Form
            </button>
        </div>
    </div>

    <script>
        // File preview functionality
        document.getElementById('photoInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            if (file) {
                preview.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
            }
        });

        document.getElementById('resumeInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('resumePreview');
            if (file) {
                preview.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
            }
        });

        function displayFilledForms() {
            document.getElementById('formSection').style.display = 'none';
            document.getElementById('filledFormsSection').style.display = 'block';
            window.history.pushState({}, '', '?show=filled');
            window.scrollTo(0, 0);
        }

        function backToForm() {
            document.getElementById('formSection').style.display = 'block';
            document.getElementById('filledFormsSection').style.display = 'none';
            window.history.pushState({}, '', '?');
            const successMsg = document.querySelector('.success-message');
            if (successMsg) {
                successMsg.style.display = 'none';
            }
            window.scrollTo(0, 0);
        }

        function viewForm(index) {
            const forms = <?php echo json_encode($savedForms); ?>;
            if (forms[index]) {
                const form = forms[index];
                const photoHtml = form.photo_path ? `<img src="${form.photo_path}" alt="Profile" class="detail-photo">` : '';
                
                const details = `
                    <div class="form-details">
                        <div class="form-details-header">
                            ${photoHtml}
                            <div class="detail-header-text">
                                <h2>${form.username}</h2>
                                <p>
                                    <i class="fas fa-calendar-alt"></i>
                                    Saved on ${new Date(form.timestamp * 1000).toLocaleString()}
                                </p>
                            </div>
                        </div>
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-envelope"></i> Email</div>
                                <div class="detail-value">${form.email}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-id-card"></i> USN</div>
                                <div class="detail-value">${form.usn}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-venus-mars"></i> Gender</div>
                                <div class="detail-value">${form.gender}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-birthday-cake"></i> Date of Birth</div>
                                <div class="detail-value">${form.dob}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-code"></i> Technical Skills</div>
                                <div class="detail-value">${form.languages.join(', ')}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-file"></i> Resume</div>
                                <div class="detail-value">
                                    ${form.resume_path ? '<a href="' + form.resume_path + '" class="resume-link" download><i class="fas fa-download"></i> Download Resume</a>' : 'N/A'}
                                </div>
                            </div>
                            <div class="detail-item detail-item-full">
                                <div class="detail-label"><i class="fas fa-pen-fancy"></i> About</div>
                                <div class="detail-value">${form.description}</div>
                            </div>
                        </div>
                    </div>
                `;
                const formList = document.querySelector('.form-list');
                formList.insertAdjacentHTML('beforebegin', details);
                formList.style.display = 'none';
            }
        }
    </script>
</body>
</html>
