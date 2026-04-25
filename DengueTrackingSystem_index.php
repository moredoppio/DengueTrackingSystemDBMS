<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = new mysqli("localhost", "root", "", "DengueTrackingSystem");
$total_users = 0;
if (!$conn->connect_error) {
    $count_query = $conn->query("SELECT COUNT(*) AS total FROM Users");
    $row = $count_query->fetch_assoc();
    $total_users = $row['total'] ?? 0;
    $conn->close(); // Added this to close connection
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8"> 
    <title>Dengue DBMS - Login</title> 
    <!-- Fixed the Font URL here -->
    <link href="https://googleapis.com" rel="stylesheet"> 
    <style> 
        body { background-color: #FDFD96; font-family: 'Architects Daughter', cursive; display: flex; flex-direction: column; align-items: center; padding: 50px; margin: 0; }
        #toggle-login { display: none; }
        .registry-summary { width: 350px; background-color: #FFE45E; border: 3px solid #FF6392; box-shadow: 5px 5px 0px #FF6392; cursor: pointer; padding: 20px 0; text-align: center; color: #5AA9E6; display: block; border-radius: 2px; }
        .user-count { font-size: 2.5rem; font-weight: bold; color: #FF6392; margin: 10px 0; }
        .erd-container { width: 350px; background-color: #FFE45E; border: 3px solid #FF6392; box-shadow: 5px 5px 0px #FF6392; border-radius: 2px; max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.5s ease, opacity 0.3s ease; }
        #toggle-login:checked + .registry-summary + .erd-container { max-height: 600px; opacity: 1; margin-top: 25px; }
        .form-row { display: flex; align-items: center; border-bottom: 1px solid rgba(255, 99, 146, 0.3); padding: 8px 10px; } 
        .prefix { width: 40px; font-size: 0.8rem; color: #FF6392; font-weight: bold; text-align: center; } 
        input, select { flex-grow: 1; background: transparent; border: none; font-family: 'Architects Daughter', cursive; color: #5AA9E6; font-size: 1.1rem; outline: none; }
        .submit-btn { width: 100%; background-color: #5AA9E6; color: white; border: none; padding: 15px; font-family: 'Architects Daughter', cursive; font-size: 1.2rem; cursor: pointer; } 
    </style>
</head>
<body>
    <input type="checkbox" id="toggle-login">
    <label class="registry-summary" for="toggle-login">
        <h2>Registered Users</h2>
        <div class="user-count"><?php echo $total_users; ?></div> 
        <p>Click to Login</p>
    </label>

    <form class="erd-container" action="login_handler.php" method="POST"> 
        <div class="erd-header"></div> 
        <div class="form-row">
          <div class="prefix">NN</div> 
          <select name="role" required> 
            <option value="" disabled selected>role</option>
            <option value="Admin">Admin</option>
            <option value="Doctor">Doctor</option>
            <option value="Nurse">Nurse</option>
            <option value="Technician">Technician</option>
          </select> 
        </div>
        <div class="form-row">
            <div class="prefix">FK</div> 
            <!-- Note: hospital_id is optional here so Admins can leave it blank -->
            <input type="number" name="hospital_id" placeholder="hospital id (if not Admin)">
        </div>
        <div class="form-row">
            <div class="prefix">NN</div> 
            <input type="text" name="username" placeholder="username" required>
        </div>
        <div class="form-row">
            <div class="prefix">NN</div> 
            <input type="password" name="password" placeholder="password" required>
        </div>
        <button type="submit" class="submit-btn">Enter DBMS</button>
    </form>

<div style="margin-top: 30px;">
    <form action="log_out_handler.php" method="POST">
        <button type="submit" style="
            background-color: #FF6392; 
            color: white; 
            border: 3px solid #5AA9E6; 
            padding: 10px 20px; 
            font-family: 'Architects Daughter', cursive; 
            cursor: pointer;
            box-shadow: 4px 4px 0px #5AA9E6;
        ">DROP SESSION (Logout)</button>
    </form>
</div>

</body>
</html>