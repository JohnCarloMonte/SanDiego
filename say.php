<?php
session_start();
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch existing user information
$sql = "SELECT first_name, middle_name, last_name, gender, province, city, barangay, id_upload_path, source_of_income, average_income, selfie FROM users WHERE username='$username'";
$result = $conn->query($sql);

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
    
    // Validate inputs (example: average income must be a valid number)
    if (!is_numeric($average_income) || $average_income < 0) {
        echo "Invalid Average Income Amount.";
        exit();
    }

    // ID Upload Handling (if any)
    $id_upload = $user['id_upload_path']; // Use existing upload if not updated
    if (isset($_FILES['id_upload']) && $_FILES['id_upload']['error'] == UPLOAD_ERR_OK) {
        $id_upload = $_FILES['id_upload']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($id_upload);
        move_uploaded_file($_FILES["id_upload"]["tmp_name"], $target_file);
    }

    // Handle Selfie Upload
    $selfie_path = $user['selfie']; // Use existing selfie if not updated
    if (isset($_FILES['selfie_upload']) && $_FILES['selfie_upload']['error'] == UPLOAD_ERR_OK) {
        $selfie = $_FILES['selfie_upload']['name'];
        $target_dir = "uploads/selfies/";
        $target_file = $target_dir . basename($selfie);

        // Ensure selfies folder exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move the uploaded file to the selfies folder
        if (move_uploaded_file($_FILES["selfie_upload"]["tmp_name"], $target_file)) {
            $selfie_path = $target_file;
        }
    }

    // Update user info in the database with the session's username
    $sql = "UPDATE users SET 
                first_name='$first_name', 
                middle_name='$middle_name', 
                last_name='$last_name', 
                gender='$gender', 
                province='$province', 
                city='$city', 
                barangay='$barangay', 
                id_upload_path='$id_upload', 
                source_of_income='$source_of_income', 
                average_income='$average_income',
                selfie='$selfie_path'
            WHERE username='$username'";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // Redirect or give a success message
        echo "Profile updated successfully!";
        header("Location: Mainpage.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Diego - Something About You</title>
    <!-- Link to Google Fonts for Open Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            position: relative; /* To position background circles absolutely within the body */
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll due to circles */
            background-color: #ffffff; /* Base background color */
        }

        /* Background Circles */
        .background-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6; /* Adjust opacity for overlapping effect */
            z-index: -1; /* Ensure circles are behind all content */
        }

        /* First Circle */
        .circle1 {
            width: 150vw;
            height: 700px;
            background-color: #f9dd4e; /* Yellow */
            bottom: 1300px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 0.6; /* Corrected opacity */
        }

        /* Second Circle */
        .circle2 {
            width: 150vw;
            height: 700px;
            background-color: #f7e96c; /* Gold */
            bottom: 1100px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Third Circle */
        .circle3 {
            width: 150vw;
            height: 700px;
            background-color: #FFFCC9; /* Light Yellow */
            bottom: 1000px;
            left: 50%;
            transform: translateX(-50%) translateY(50%);
            opacity: 1; /* Corrected opacity */
        }

        /* Navbar container */
        .navbar {
            display: flex;
            justify-content: space-between; /* Space between left and center */
            align-items: center;
            padding: 10px 20px;
            background-color: rgba(248, 249, 250, 0.9); /* Slight transparency to show background */
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000; /* Ensure navbar is on top */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Left Side of Navbar */
        .nav-left {
            flex: 1;
            text-align: left;
        }

        /* Center of Navbar */
        .nav-center {
            flex: 1;
            text-align: center;
        }

        /* Right Side of Navbar (Empty for now) */
        .nav-right {
            flex: 1;
            text-align: right;
        }

        /* Website name */
        .site-name {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        /* Navigation Button Styling */
        .nav-btn {
            background-color: #F5E071; /* Same as login button */
            padding: 10px 15px;
            border-radius: 50px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .nav-btn:hover {
            background-color: #e6d165;
        }

        /* Content Container */
        .content {
            padding: 100px 20px 20px 20px; /* Top padding accounts for fixed navbar */
            min-height: 100vh;
            position: relative;
            z-index: 1; /* Ensure content is above background circles */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Main Container for Two Columns */
        .main-container {
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Text Content Styling */
        .text-content {
    flex: 1;
    min-width: 250px;
    position: relative;
    margin-left: 80px;
    margin-top: -95vh; /* Move upward */
    font-size: 30px;
}

        /* Something About You Content Styling */
        .aboutyou-content {
            flex: 1;
            min-width: 300px; /* Adjust based on form width */
            display: flex;
            justify-content: center; /* Center the form horizontally */
            width: 100%;
        }

        /* Something About You Form Container */
        .aboutyou-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        /* Form Heading */
        .aboutyou-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        /* Form Styling */
        .aboutyou-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .aboutyou-form label {
            font-size: 1rem;
            color: #333333;
            margin-bottom: 5px;
        }

        .aboutyou-form input[type="text"],
        .aboutyou-form input[type="number"],
        .aboutyou-form input[type="file"] {
            padding: 10px;
            border: 2px solid #F5E071; /* Outline color */
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .aboutyou-form input[type="text"]:focus,
        .aboutyou-form input[type="number"]:focus,
        .aboutyou-form input[type="file"]:focus {
            border-color: #e6d165; /* Darker outline on focus */
            outline: none;
        }

        /* Gender Checkbox Styling */
        .gender-options {
            display: flex;
            gap: 20px;
        }

        .gender-options label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 1rem;
            color: #333333;
        }

        /* Address Fields Group */
        .address-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ID Upload Label */
        .id-upload-label {
            font-size: 1rem;
            color: #333333;
        }

        /* Sign Up Button */
        .aboutyou-form button {
            padding: 10px;
            background-color: #F5E071;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .aboutyou-form button:hover {
            background-color: #e6d165;
        }

        /* Secondary Buttons Styling */
        .secondary-btns {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        /* New Class for Outline Button */
        .outline-btn {
            background-color: #ffffff; /* White background */
            border: 2px solid #F5E071; /* Outline color */
            color: black; /* Text color matches outline */
            padding: 8px 12px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
            font-size: 0.9rem;
        }

        .outline-btn:hover {
            background-color: #F5E071; /* Fill background on hover */
            color: black; /* Change text color on hover */
        }

        /* Success Message Styling */
        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        /* Error Message Styling */
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                gap: 50px; /* Reduced space between stacked elements */
                align-items: center;
            }
            .nav-btn {
                padding: 2px;
            }

            /* Adjust Something About You Content for mobile */
            .aboutyou-content {
                width: 100%;
                justify-content: center; /* Ensure form is centered */
            }

            /* Adjust Something About You Form for mobile */
            .aboutyou-container {
                max-width: 100%; /* Ensure it takes full width on mobile */
                width: 100%;
            }

            /* Text Content Alignment on Mobile */
            .text-content {
                text-align: center;
                margin-left: 0px; /* Center text on mobile for better aesthetics */
            }

            .text-content h1 {
                font-size: 2rem; /* Adjust heading size for mobile */
            }

            .text-content p {
                font-size: 1rem; /* Adjust paragraph size for mobile */
            }

            /* Stack Secondary Buttons Vertically on Mobile */
            .secondary-btns {
                flex-direction: column;
                gap: 10px;
            }

            .secondary-btn,
            .outline-btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Focus States for Accessibility */
        .aboutyou-form input:focus,
        .aboutyou-form button:focus,
        .nav-btn:focus,
        .outline-btn:focus {
            outline: 2px solid #F5E071;
        }
                        /* Final Text */
                        .final-text {
            text-align: center;
            font-size: 1.5rem;
            color: #333333;
            margin-top: 40px;
            font-weight: bold;
        }

        /* Flag Container */
        .flag-container {
            justify-content: center;
            display: flex;
            gap: 20px; /* Space between flags */
        }

        /* Flag Links */
        .flag-link {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .flag-link:hover,
        .flag-link:focus {
            transform: scale(1.1); /* Slightly enlarge on hover/focus */
        }

        /* Flag Icons */
        .flag-icon {
            width: 30px; /* Adjust size as needed */
            height: auto;
        }

        /* Responsive Adjustments for Flags */
        @media (max-width: 768px) {
            .flag-icon {
                width: 25px; /* Smaller flags on mobile */
            }
        }

        @media (max-width: 480px) {
            .flag-icon {
                width: 20px; /* Even smaller flags on very small screens */
            }
        }
        .address-section {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px; /* Adjust margin as needed */
    }

    .address-section label {
        font-size: 1rem;
        color: #333333;
        margin-bottom: 5px;
    }

    .address-section select {
        padding: 10px;
        border: 2px solid #F5E071; /* Outline color */
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
        width: 100%; /* Full width */
    }

    .address-section select:focus {
        border-color: #e6d165; /* Darker outline on focus */
        outline: none;
    }
    .logo-print {
                display: block;
                margin: 0 auto;
                width: 200px; /* Adjust size as needed */
            }
    </style>
</head>
<body>

    <!-- Background Circles -->
    <div class="background-circle circle1"></div>
    <div class="background-circle circle2"></div>
    <div class="background-circle circle3"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <!-- Left Side of Navbar -->
        <div class="nav-left">
        <a href="Home.php" class="site-name" onclick="location.reload()">San Diego</a>
        </div>

        <!-- Center of Navbar -->
        <div class="nav-center">
       
        </div>

        <!-- Right Side of Navbar (Empty for now) -->
        <div class="nav-right">
            <!-- Placeholder for future navigation items -->
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
    <!-- Main Container for Two Columns -->
    <div class="main-container">
        <!-- Text Content (Optional) -->
        <div class="text-content">
        <img src="logo.png" class="logo-print" alt="Logo">
            <h1 id="firsth1">Something About You</h1>
            <p>Help us get to know you better by providing some additional information.</p>
        </div>

        <!-- Something About You Content (Form) -->
        <div class="aboutyou-content">
            <div class="aboutyou-container" aria-labelledby="aboutyou-heading">
                <h2 id="aboutyou-heading">Your Details</h2>

                <!-- Display Success Message if Any -->
                <?php
                    if (!empty($success)) {
                        echo '<div class="success">' . $success . '</div>';
                    }

                    // Display Error Messages if Any
                    if (!empty($errors)) {
                        echo '<div class="error"><ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                ?>

                <!-- Something About You Form -->
                <form class="aboutyou-form" method="POST" action="say.php" enctype="multipart/form-data" aria-labelledby="aboutyou-heading">
    <!-- Name Section -->
    <div class="name-section">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>

        <label for="middle_name">Middle Name</label>
        <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middle name">

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
    </div>

    <!-- Gender Section -->
    <div class="gender-section">
        <label>Gender</label>
        <div class="gender-options">
            <label><input type="radio" name="gender" value="Male" required> Male</label>
            <label><input type="radio" name="gender" value="Female" required> Female</label>
        </div>
    </div>

    <div class="income-section">
        <label for="source_of_income">Source of Income</label>
        <input type="text" id="source_of_income" name="source_of_income" placeholder="Enter your source of income" required>

        <label for="average_income">Average Monthly Income</label>
        <input type="number" id="average_income" name="average_income" placeholder="Enter your average income amount" min="500" required>
    </div>

    <!-- Address Section -->
    <div class="address-section">
        <label for="province">Province</label>
        <select id="province" name="province" required>
            <option value="Camarines Norte" selected>Camarines Norte</option>
        </select>

        <label for="city">City/Municipality</label>
        <select id="city" name="city" onchange="updateBarangays()" required>
            <option value="Basud">Basud</option>
            <option value="Capalonga">Capalonga</option>
            <option value="Daet">Daet</option>
            <option value="Jose Panganiban">Jose Panganiban</option>
            <option value="Labo">Labo</option>
            <option value="Mercedes">Mercedes</option>
            <option value="Paracale">Paracale</option>
            <option value="San Lorenzo Ruiz">San Lorenzo Ruiz</option>
            <option value="San Vicente">San Vicente</option>
            <option value="Santa Elena">Santa Elena</option>
            <option value="Talisay">Talisay</option>
            <option value="Vinzons">Vinzons</option>
        </select>

        <label for="barangay">Barangay</label>
        <select id="barangay" name="barangay" required>
            <!-- Barangays will be populated based on selected municipality -->
        </select>
    </div>
    <div class="id-upload-section">
                        <label class="id-upload-label" for="id_upload">ID Upload</label>
                        <input type="file" id="id_upload" name="id_upload" accept="image/*" required>
                    </div>

    <!-- Selfie Upload Section -->
    <div class="selfie-upload-section">
        <label class="selfie-upload-label" for="selfie_upload">Upload Your Selfie</label>
        <input type="file" id="selfie_upload" name="selfie_upload" accept="image/*" required>
    </div>

    <!-- Sign Up Button -->
    <button type="submit">Sign Up</button>

    <!-- Secondary Buttons -->
    <div class="secondary-btns">
        <a href="Login.php" class="outline-btn">Already have an account? Login</a>
        <a href="cpassword.php" class="outline-btn">Back to Password Creation</a>
    </div>
</form>

            </div>
        </div>
    </div>
</div>
            <!-- Final Text -->
            <div class="final-text">
            Building Trust, Funding Dreams.
            <p>San Diego | 2024</p>
        </div>
    </div>


        <div class="flag-container">
            <a href="#" class="flag-link" aria-label="United States">
                <img src="united-states.png" alt="United States Flag" class="flag-icon">
            </a>
            <a href="#" class="flag-link" aria-label="Philippines">
                <img src="philippines.png" alt="Philippines Flag" class="flag-icon">
            </a>
        </div>

    <!-- Optional JavaScript for Accessibility Enhancements -->
    <script>
        // Allow keyboard navigation for the form
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.aboutyou-form');
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });
        });
        const barangays = {
        "Basud": ["Angas", "Binatug", "Caayunan", "Guinatungan", "Hinampacan", "Laniton", "Mampurog", "Mantugawe", "Matnog", "Oliva", "Plaridel", "San Felipe", "San Jose", "Tacad", "Taisan"],
        "Capalonga": ["Alayao", "Camagsaan", "Catabaguangan", "Del Pilar", "Itok", "Mabini", "Mactang", "Mataque", "Old Camp", "Poblacion", "San Antonio", "San Isidro", "Tanauan", "Itok", "Villa Belen", "Villa Aurora"],
        "Daet": ["Alawihao", "Awitan", "Bagasbas", "Barangay I", "Barangay II", "Barangay III", "Barangay IV", "Barangay V", "Barangay VI", "Camambugan", "Cobangbang", "Dogongan", "Gahonon", "Lag-on", "Magang"],
        "Jose Panganiban": ["Bagong Bayan", "Calero", "Dayhagan", "Larap", "Luklukan Norte", "Luklukan Sur", "Motherlode", "Osmeña", "Parang", "Plaridel", "Salvacion", "San Isidro", "Santa Cruz", "Santa Elena", "Tamisan"],
        "Labo": ["Anameam", "Baay", "Bagacay", "Baay", "Cabusay", "Calabasa", "Calapugan", "Camagong", "Canapawan", "Daguit", "Fundado", "Gumamela", "Mabilo I", "Mabilo II", "Malasugui"],
        "Mercedes": ["Apuao", "Bangad", "Colasi", "Mambungalon", "Manlimonsito", "Masalongsalong", "Pambuhan", "Quinapaguian", "San Roque", "Tarum"],
        "Paracale": ["Awitan", "Batobalani", "Capacuan", "Dagang", "Dalnac", "Gumaus", "Labnig", "Lanot", "Macolabo", "Malacbang", "Malatap", "Matacong", "Palanas", "Poblacion Norte", "Poblacion Sur", "Pulangdaga", "Tawig"],
        "San Lorenzo Ruiz": ["Dagotdotan", "Langga", "Maisog", "Matacong", "Matogdon", "Nakalaya", "Salvacion", "San Isidro", "San Ramon", "San Vicente", "Santol", "Vinzons"],
        "San Vicente": ["Alayao", "Asdum", "Bagong Silang", "Camagsaan", "Pambujan", "Manmubong"],
        "Santa Elena": ["Don Tomas", "Rizal", "San Vicente", "Villa Belen"],
        "Talisay": ["Binanwaan", "Del Rosario", "Lalawigan", "Sinipian", "Tabugon", "Poblacion", "Sta. Elena"],
        "Vinzons": ["Aguit-it", "Banocboc", "Calangcawan Norte", "Calangcawan Sur", "Guinacutan", "Magpanit", "Mangcayo", "Manlucugan", "Pinagtigasan", "Sabang", "Santo Domingo", "Talusan"]
    };

    function updateBarangays() {
        const municipality = document.getElementById("city").value;
        const barangaySelect = document.getElementById("barangay");

        // Clear previous options
        barangaySelect.innerHTML = "";

        // Populate barangay options
        if (barangays[municipality]) {
            barangays[municipality].forEach(barangay => {
                const option = document.createElement("option");
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });
        }
    }

    // Initialize barangays based on the default selected municipality
    document.addEventListener("DOMContentLoaded", updateBarangays);
    </script>

</body>
</html>
