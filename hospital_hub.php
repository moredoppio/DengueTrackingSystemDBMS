<?php
$conn = new mysqli("localhost", "root", "", "DengueTrackingSystem");

// 1. Capture Filters (Restoring your original variables)
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_corp = isset($_GET['filter_corp']) ? $_GET['filter_corp'] : '';

// 2. Build Filter Logic (Restoring wildcard and specific fields)
$conditions = [];
if ($search_name !== '') {
    $escaped = $conn->real_escape_string($search_name);
    $wildcard = (strpos($escaped, '%') !== false) ? $escaped : "%$escaped%";
    $conditions[] = "H.hospital_name LIKE '$wildcard'";
}
if ($filter_type !== '') {
    $conditions[] = "H.hospital_type = '" . $conn->real_escape_string($filter_type) . "'";
}
if ($filter_corp !== '') {
    $conditions[] = "H.corporation = '" . $conn->real_escape_string($filter_corp) . "'";
}

$where = (count($conditions) > 0) ? " WHERE " . implode(" AND ", $conditions) : "";

// 3. Dynamic Counts (Linking all 3 cards)
$countHospitals = $conn->query("SELECT COUNT(*) as total FROM Hospitals H $where")->fetch_assoc()['total'];
$countUsers     = $conn->query("SELECT COUNT(*) as total FROM Users U JOIN Hospitals H ON U.hospital_id = H.hospital_id $where")->fetch_assoc()['total'];
$countBlood     = $conn->query("SELECT COUNT(*) as total FROM BloodInventory B JOIN Hospitals H ON B.hospital_id = H.hospital_id $where")->fetch_assoc()['total'];

$corps = ['Dhaka North City Corporation (DNCC)', 'Dhaka South City Corporation (DSCC)', 'Chattogram City Corporation (CCC)', 'Khulna City Corporation (KCC)', 'Rajshahi City Corporation (RCC)', 'Sylhet City Corporation (SCC)', 'Barishal City Corporation (BCC)', 'Rangpur City Corporation (RpCC)', 'Comilla City Corporation (CuCC)', 'Narayanganj City Corporation (NCC)', 'Gazipur City Corporation (GCC)', 'Mymensingh City Corporation (MCC)'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Hub ERD</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        body { background-color: #FDFD96; font-family: 'Architects Daughter', cursive; display: flex; flex-direction: column; align-items: center; padding: 20px; margin: 0; }
        
        /* RESTORED GRID dimensions (250px 60px 250px 60px 250px) */
        .map-canvas {
            display: grid;
            grid-template-columns: 250px 60px 250px 60px 250px;
            grid-template-rows: 100px 60px auto 60px 100px; /* Center row auto-adjusts for search */
            align-items: center;
            justify-items: center;
            margin-top: 50px;
        }

        .erd-container { 
            width: 100%; height: 100%; min-height: 100px;
            background-color: #FFE45E; border: 3px solid #FF6392; 
            box-shadow: 4px 4px 0px #FF6392; border-radius: 2px;
            text-decoration: none; display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 5;
        }

        /* Search input styling inside the card */
        .card-form { width: 90%; display: flex; flex-direction: column; gap: 5px; padding-bottom: 10px; }
        .card-form input, .card-form select { 
            font-family: inherit; font-size: 0.75rem; padding: 4px; border: 2px solid #FF6392; border-radius: 3px;
        }
        .card-form button { background: #FF6392; color: white; border: none; padding: 5px; font-weight: bold; cursor: pointer; }

        .erd-header { font-weight: bold; font-size: 1.1rem; color: #5AA9E6; }
        .row-count { color: #FF6392; font-size: 0.9rem; font-weight: bold; }
        
        #users { grid-area: 3 / 5; }
        #blood { grid-area: 3 / 1; }
        #hospitals { grid-area: 3 / 3; height: auto; padding: 10px 0; }

        .line-h { height: 4px; width: 100%; background: #FF6392; display: flex; align-items: center; justify-content: center; }
        .line-h::after { 
            color: #FF6392; background: #FDFD96; 
            font-weight: bold; padding: 2px 4px; font-size: 0.7rem; 
            border: 2px solid #FF6392; border-radius: 3px; 
        }
        .arrow-one { grid-area: 3 / 2; }
        .arrow-two { grid-area: 3 / 4; }
        .arrow-one::after { content: 'FK ←'; } 
        .arrow-two::after { content: 'FK →'; }

        .back-btn { margin-top: 20px; padding: 10px 20px; background-color: #5AA9E6; color: white; text-decoration: none; border: 2px solid #FF6392; box-shadow: 3px 3px 0px #FF6392; }
    </style>
</head>
<body>

    <a href="dashboard.php" class="back-btn">Back to Main Hub</a>
    <h1 style="color: #5AA9E6;">Hospital Hub Database</h1>

    <div class="map-canvas">
        <!-- Blood Inventory -->
        <a href="blood_inventory_registry.php" class="erd-container" id="blood">
            <div class="erd-header">🩸Hospital Blood Inventory</div>
            <div class="row-count">Records: <?php echo $countBlood; ?></div>
        </a>

        <div class="line-h arrow-one"></div>

        <!-- Hospital Registry (Filters Inside) -->
        <div class="erd-container" id="hospitals">
            <div class="erd-header">Hospital Registry</div>
            <div class="row-count">Matches: <?php echo $countHospitals; ?></div>
            
            <form method="GET" class="card-form">
                <input type="text" name="search_name" placeholder="Name (e.g. Dhaka%)" value="<?php echo htmlspecialchars($search_name); ?>">
                
                <select name="filter_type">
                    <option value="">-- All Types --</option>
                    <option value="Public" <?= $filter_type == 'Public' ? 'selected' : '' ?>>Public</option>
                    <option value="Private" <?= $filter_type == 'Private' ? 'selected' : '' ?>>Private</option>
                    <option value="Clinic" <?= $filter_type == 'Clinic' ? 'selected' : '' ?>>Clinic</option>
                </select>

                <select name="filter_corp">
                    <option value="">-- All Corporations --</option>
                    <?php foreach($corps as $c): ?>
                        <option value="<?= $c ?>" <?= ($filter_corp == $c) ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Update Hub</button>
                <a href="?" style="text-align:center; font-size:0.7rem; color:#FF6392; text-decoration:none;">Reset All</a>
            </form>
            <a href="hospital_registry.php" style="font-size: 0.7rem; color: #5AA9E6;">Open Full Registry →</a>
        </div>

        <div class="line-h arrow-two"></div>

        <!-- User Registry -->
        <a href="users_entry.php" class="erd-container" id="users">
            <div class="erd-header">User Registry</div>
            <div class="row-count">Staff: <?php echo $countUsers; ?></div>
        </a>
    </div>

</body>
</html>

