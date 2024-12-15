<?php
session_start();

// Include connection to the database
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

// Fetch existing user information
$username = $_SESSION['username'];
$sql = "SELECT first_name, middle_name, last_name, gender, province, city, barangay, id_upload_path, source_of_income, average_income, selfie FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch user data
    $user = $result->fetch_assoc();
} else {
    echo "No user found.";
    exit();
}

// Handle form submission for updating user info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $gender = trim($_POST['gender']);
    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $barangay = trim($_POST['barangay']);
    $source_of_income = trim($_POST['source_of_income']);
    $average_income = trim($_POST['average_income']);

    // Validate inputs
    if (!is_numeric($average_income) || $average_income < 0) {
        echo "Invalid Average Income Amount.";
        exit();
    }

    // ID Upload Handling (if any)
    $id_upload = $user['id_upload_path']; // Use existing upload if not updated
    if (isset($_FILES['id_upload']) && $_FILES['id_upload']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['id_upload']['tmp_name'];
        $file_name = $_FILES['id_upload']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types for ID upload
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($file_name);

            // Check if file already exists
            if (file_exists($target_file)) {
                echo "File already exists.";
                exit();
            }

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $target_file)) {
                $id_upload = $file_name; // Update file name if uploaded successfully
            } else {
                echo "Error uploading ID file.";
                exit();
            }
        } else {
            echo "Invalid file type for ID upload. Only JPG, PNG, or GIF allowed.";
            exit();
        }
    }

    // Selfie Upload Handling
    $selfie_path = $user['selfie']; // Use existing selfie if not updated
    if (isset($_FILES['selfie_upload']) && $_FILES['selfie_upload']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['selfie_upload']['tmp_name'];
        $file_name = $_FILES['selfie_upload']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file types for Selfie upload
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $target_dir = "uploads/selfies/";
            $target_file = $target_dir . basename($file_name);

            // Ensure selfies folder exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $target_file)) {
                $selfie_path = $file_name; // Update file name if uploaded successfully
            } else {
                echo "Error uploading selfie.";
                exit();
            }
        } else {
            echo "Invalid file type for selfie. Only JPG, PNG, or GIF allowed.";
            exit();
        }
    }

    // Update user info in the database using prepared statements
    $sql = "UPDATE users SET 
                first_name=?, 
                middle_name=?, 
                last_name=?, 
                gender=?, 
                province=?, 
                city=?, 
                barangay=?, 
                id_upload_path=?, 
                source_of_income=?, 
                average_income=?, 
                selfie=? 
            WHERE username=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssdss", $first_name, $middle_name, $last_name, $gender, $province, $city, $barangay, $id_upload, $source_of_income, $average_income, $selfie_path, $username);

    if ($stmt->execute()) {
        header("Location: Mainpage.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Information</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* Reset some default styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #ffffff;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: rgba(248, 249, 250, 0.9);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar a { text-decoration: none; color: black; }

        .navbar .site-name {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .logout-btn {
            background-color: #F5E071;
            padding: 10px 15px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .navbar .logout-btn:hover {
            background-color: #e6d165;
        }

        /* Form Container */
        .form-container {
            margin: 80px auto;
            max-width: 600px;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            margin: 10px 0 5px;
        }

        .form-container input[type="text"],
        .form-container input[type="file"],
        .form-container input[type="email"],
        .form-container input[type="number"],
        .form-container input[type="radio"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #F5E071;
            border-radius: 5px;
        }

        .form-container button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #F5E071;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            color: black;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #e6d165;
        }

        .secondary-btns {
            text-align: center;
            margin-top: 15px;
        }

        .secondary-btns a {
            color: black;
            text-decoration: none;
            font-weight: bold;
        }
        #id_preview_container {
    margin-top: 10px;
    text-align: center;
}

#id_preview {
    max-width: 100%;
    max-height: 200px;
    border: 2px solid #F5E071;
    border-radius: 5px;
}

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="#" class="site-name">San Diego</a>
    <a href="Mainpage.php" class="logout-btn">Logout</a>
</nav>

<!-- Form Container -->
<div class="form-container">
    <h2>Update Your Information</h2>
    <form method="POST" action="updateinfo.php" enctype="multipart/form-data">
    <!-- Name Fields -->
    <label for="first_name">First Name</label>
    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

    <label for="middle_name">Middle Name</label>
    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">

    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

    <!-- Gender Section -->
    <label>Gender</label>
    <div>
        <label style="display: inline-block; margin-right: 15px;">
            <input type="radio" name="gender" value="Male" <?php echo $user['gender'] == 'Male' ? 'checked' : ''; ?>> 
            Male
        </label>
        <label style="display: inline-block;">
            <input type="radio" name="gender" value="Female" <?php echo $user['gender'] == 'Female' ? 'checked' : ''; ?>> 
            Female
        </label>
    </div>

    <!-- Address & Income Fields -->
    <label for="province">Province</label>
    <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user['province']); ?>" required>

    <label for="city">City/Municipality</label>
    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>

    <label for="barangay">Barangay</label>
    <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>" required>

    <label for="source_of_income">Source of Income</label>
    <input type="text" id="source_of_income" name="source_of_income" value="<?php echo htmlspecialchars($user['source_of_income']); ?>" required>

    <label for="average_income">Average Income Amount</label>
    <input type="number" id="average_income" name="average_income" step="0.01" value="<?php echo htmlspecialchars($user['average_income']); ?>" required>

    <!-- ID Upload Section -->
    <label for="id_upload">ID Upload</label>
    <input type="file" id="id_upload" name="id_upload" accept="image/*" onchange="previewID(this)">
    <div id="id_preview_container">
        <?php if (!empty($user['id_upload_path'])): ?>
            <img id="id_preview" src="uploads/<?php echo htmlspecialchars($user['id_upload_path']); ?>" alt="Uploaded ID Preview">
        <?php else: ?>
            <img id="id_preview" src="#" alt="Uploaded ID Preview" style="display: none;">
        <?php endif; ?>
    </div>

    <!-- Selfie Upload Section -->
    <label for="selfie_upload">Selfie Upload</label>
    <input type="file" id="selfie_upload" name="selfie_upload" accept="image/*" onchange="previewSelfie(this)">
    <div id="selfie_preview_container">
        <?php if (!empty($user['selfie'])): ?>
            <img id="selfie_preview" src="uploads/selfies/<?php echo htmlspecialchars($user['selfie']); ?>" alt="Uploaded Selfie Preview" style="max-width: 100%; height: auto;">
        <?php else: ?>
            <img id="selfie_preview" src="#" alt="Uploaded Selfie Preview" style="display: none; max-width: 100%; height: auto;">
        <?php endif; ?>
    </div>

    <button type="submit">Update Info</button>

    <div class="secondary-btns">
        <a href="Mainpage.php">Back to Home</a>
    </div>
</form>

</div>

</body>
<script>
    // Preview for ID Upload
    function previewID(input) {
        const preview = document.getElementById('id_preview');
        const previewContainer = document.getElementById('id_preview_container');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
        }
    }

    // Preview for Selfie Upload
    function previewSelfie(input) {
        const preview = document.getElementById('selfie_preview');
        const previewContainer = document.getElementById('selfie_preview_container');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block'; // Show the image preview
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.style.display = 'none'; // Hide the image preview if no file is selected
        }
    }
</script>


</html>
