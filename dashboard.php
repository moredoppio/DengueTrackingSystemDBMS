<?php
session_start();

// Check if the user is logged in by verifying if 'user_id' exists in the session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect them back to the login page
    header("Location: DengueTrackingSystem_index.php");
    exit();
}

// Optional: Create shorter variables for use in your HTML below
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];
$hospitalId = $_SESSION['hospital_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dengue DBMS - Dashboard</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        body { background-color: #FDFD96; font-family: 'Architects Daughter', cursive; padding: 50px; margin: 0; color: #5AA9E6; }

        .welcome { text-align: center; margin-bottom: 40px; }
        
        /* Layout for ID Card and Hubs */
        .main-content { display: flex; flex-direction: column; align-items: center; gap: 40px; }
        .hub-container { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
        
        /* The ID Card Styles */
        .key-card {
            width: 300px;
            background-color: #FFE45E;
            border: 3px solid #FF6392;
            box-shadow: 8px 8px 0px #FF6392;
            padding: 20px;
            border-radius: 2px;
            text-align: left;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 20px;
        }
        .id-card:hover { transform: translate(-3px, -3px); box-shadow: 11px 11px 0px #5AA9E6; }
        .card-header { font-size: 1.2rem; color: #FF6392; border-bottom: 2px dashed #FF6392; margin-bottom: 15px; padding-bottom: 5px; font-weight: bold; }
        .info-label { color: #5AA9E6; font-size: 0.9rem; margin-top: 10px; }
        .info-value { font-size: 1.4rem; color: #333; margin-bottom: 5px; }

        /* Hub Card Styles */
        .hub-card { 
            width: 280px; background-color: #FFE45E; border: 3px solid #FF6392; 
            box-shadow: 8px 8px 0px #FF6392; padding: 20px; border-radius: 2px;
            line-height: 1.6;
        }
        .hub-card h2 { border-bottom: 2px solid #FF6392; padding-bottom: 10px; margin-top: 0; }
        
        /* Title Links Only */
        .hub-card a { text-decoration: none; color: #FF6392; }
        .hub-card a:hover { color: #5AA9E6; }

        .logout { display: block; text-align: center; margin-top: 50px; color: #FF6392; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="welcome">
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    </div>

    <div class="main-content">
        
        <!-- User ID Card Integrated -->
        <div class="key-card">
            <div class="card-header">User Profile</div>
            
            <div class="info-label">NAME</div>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            
            <div class="info-label">ROLE</div>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
            
            <div class="info-label">HOSPITAL ID</div>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['hospital_id'] ?? 'Global'); ?></div>
        </div>

        <div class="hub-container">
            <!-- Hospital Hub -->
            <div class="hub-card">
                <a href="hospital_hub.php"><h2>🏥 Hospital Hub</h2></a>
                Hospitals Registry, User Registry, Hospital Blood Inventory
            </div>

            <!-- People Hub -->
            <div class="hub-card">
                <a href="people_hub.php"><h2>👥 People Hub</h2></a>
                People, People Phones, Emergency Contacts, Patients, Donors, Addresses, Resdies At
            </div>

            <!-- Clinical Hub -->
            <div class="hub-card">
                <a href="clinical_hub.php"><h2>🧪 Clinical Hub</h2></a>
                Encounters, Dengue Details, Treatments, Lab Tests, Daily Monitoring
            </div>
        </div>

    </div>

    <a href="log_out_handler.php" class="logout">Log Out of System</a>

</body>
</html>