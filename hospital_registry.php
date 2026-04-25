<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "DengueTrackingSystem");

// --- Fetch Table Metadata ---
$metadata = [];
$meta_res = $conn->query("SHOW COLUMNS FROM Hospitals");
while($col = $meta_res->fetch_assoc()) {
    $metadata[$col['Field']] = $col;
}

// --- 2. HANDLE INSERT ---
if (isset($_POST['add_record'])) {
    $fields = [];
    $values = [];
    foreach ($_POST as $key => $val) {
        if ($key !== 'add_record' && $val !== '' && isset($metadata[$key])) {
            $fields[] = $conn->real_escape_string($key);
            $type = $metadata[$key]['Type'];
            if (preg_match('/int|decimal|float|double/i', $type)) {
                $values[] = empty($val) ? "0" : $conn->real_escape_string($val);
            } else {
                $values[] = "'" . $conn->real_escape_string($val) . "'";
            }
        }
    }
    if (count($fields) > 0) {
        $insert_sql = "INSERT INTO Hospitals (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        $conn->query($insert_sql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// --- HANDLE UPDATE (NEW FEATURE) ---
if (isset($_POST['update_record'])) {
    $update_id = $conn->real_escape_string($_POST['update_id']);
    $updates = [];
    foreach ($_POST as $key => $val) {
        if (!in_array($key, ['update_record', 'update_id']) && isset($metadata[$key])) {
            $type = $metadata[$key]['Type'];
            $escaped_val = $conn->real_escape_string($val);
            if (preg_match('/int|decimal|float|double/i', $type)) {
                $updates[] = "$key = " . (empty($val) ? "0" : $escaped_val);
            } else {
                $updates[] = "$key = '$escaped_val'";
            }
        }
    }
    if (count($updates) > 0) {
        $update_sql = "UPDATE Hospitals SET " . implode(', ', $updates) . " WHERE hospital_id = '$update_id'";
        $conn->query($update_sql);
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        exit();
    }
}

// --- HANDLE DELETE ---
if (isset($_POST['delete_record'])) {
    $id_to_delete = $conn->real_escape_string($_POST['delete_id']);
    $delete_sql = "DELETE FROM Hospitals WHERE hospital_id = '$id_to_delete'";
    $conn->query($delete_sql);
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit();
}

// 3. SEARCH & FILTER LOGIC
$table_name = "Hospitals"; 
$search_name = $_GET['search_name'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_corp = $_GET['filter_corp'] ?? '';
$edit_id = $_GET['edit_id'] ?? null;

$conditions = [];
if ($search_name !== '') {
    $escaped = $conn->real_escape_string($search_name);
    $wildcard = (strpos($escaped, '%') !== false) ? $escaped : "%$escaped%";
    $conditions[] = "hospital_name LIKE '$wildcard'";
}
if ($filter_type !== '') {
    $conditions[] = "hospital_type = '" . $conn->real_escape_string($filter_type) . "'";
}
if ($filter_corp !== '') {
    $conditions[] = "corporation = '" . $conn->real_escape_string($filter_corp) . "'";
}

$sql = "SELECT * FROM $table_name";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$result = $conn->query($sql);
$count = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        .btn-hub { display: inline-block; padding: 10px 20px; background: #5AA9E6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-bottom: 20px; border: 2px solid transparent; transition: background 0.3s; }
        .btn-hub:hover { background: #4a90c0; }
        body { background-color: #FDFD96; font-family: 'Architects Daughter', cursive; padding: 40px; }
        .filter-box { background: #FFE45E; border: 3px solid #FF6392; padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        input, select, textarea { padding: 8px; border: 2px solid #FF6392; border-radius: 5px; font-family: inherit; background: white; }
        button { padding: 8px 20px; background: #FF6392; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: inherit; font-weight: bold; }
        .btn-add { background: #5AA9E6; }
        .btn-edit { background: #FFA500; padding: 4px 8px; font-size: 0.8em; text-decoration: none; color: white; border-radius: 5px; }
        .blueprint-table { background-color: #FFE45E; border: 3px solid #FF6392; box-shadow: 10px 10px 0px #FF6392; width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: rgba(255, 99, 146, 0.1); color: #FF6392; padding: 15px; border-bottom: 3px solid #FF6392; text-align: left;}
        td { padding: 10px; border-bottom: 1px solid rgba(255, 99, 146, 0.2); color: #5AA9E6; }
        .add-row { background-color: rgba(255, 255, 255, 0.4); }
        .add-input { width: 90%; border: 1px dashed #FF6392; font-size: 0.8em; }
        .counter { color: #FF6392; font-weight: bold; margin-bottom: 5px; }
    </style>
</head>
<body>
     
    <a href="hospital_hub.php" class="btn-hub">← Return to Hospital Hub</a>

    <h1 style="color:#FF6392">Registry: <?php echo $table_name; ?></h1>

    <div class="filter-box">
        <form method="GET" style="display: contents;">
            <input type="text" name="search_name" placeholder="Search Hospital..." value="<?php echo htmlspecialchars($search_name); ?>">
            <select name="filter_type">
                <option value="">-- All Types --</option>
                <option value="Public" <?php if($filter_type == 'Public') echo 'selected'; ?>>Public</option>
                <option value="Private" <?php if($filter_type == 'Private') echo 'selected'; ?>>Private</option>
                <option value="Clinic" <?php if($filter_type == 'Clinic') echo 'selected'; ?>>Clinic</option>
            </select>
            <select name="filter_corp">
                <option value="">-- All Corporations --</option>
                <?php
                $corps = ['Dhaka North City Corporation (DNCC)', 'Dhaka South City Corporation (DSCC)', 'Chattogram City Corporation (CCC)', 'Khulna City Corporation (KCC)', 'Rajshahi City Corporation (RCC)', 'Sylhet City Corporation (SCC)', 'Barishal City Corporation (BCC)', 'Rangpur City Corporation (RpCC)', 'Comilla City Corporation (CuCC)', 'Narayanganj City Corporation (NCC)', 'Gazipur City Corporation (GCC)', 'Mymensingh City Corporation (MCC)'];
                foreach($corps as $c) {
                    $sel = ($filter_corp == $c) ? 'selected' : '';
                    echo "<option value=\"$c\" $sel>$c</option>";
                }
                ?>
            </select>
            <button type="submit">Filter</button>
            <a href="?" style="color: #FF6392; text-decoration: none; font-weight: bold; margin-left: 10px;">Reset View</a>
        </form>
    </div>

    <div class="counter">Showing <?php echo $count; ?> results</div>

    <table class="blueprint-table">
        <thead>
            <tr>
                <?php 
                $column_names = [];
                if($result) {
                    $result->field_seek(0);
                    while ($field = $result->fetch_field()) {
                        $column_names[] = $field->name;
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                }
                ?>
                <th>Action</th>
            </tr>
            <!-- ADD NEW RECORD ROW -->
            <tr class="add-row">
                <form method="POST">
                <?php foreach($column_names as $col): 
                    $info = $metadata[$col];
                    $type = $info['Type'];
                ?>
                    <td>
                        <?php if($info['Extra'] == 'auto_increment'): ?>
                            <small style="color:#FF6392">Auto</small>
                        <?php elseif(strpos($type, 'enum') !== false): 
                            preg_match_all("/'([^']+)'/", $type, $matches);
                            ?>
                            <select name="<?php echo $col; ?>" class="add-input" required>
                                <?php foreach($matches[1] as $option): ?>
                                    <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif(preg_match('/int|decimal|float|double/i', $type)): ?>
                            <input type="number" step="any" name="<?php echo $col; ?>" class="add-input" placeholder="0">
                        <?php else: ?>
                            <input type="text" name="<?php echo $col; ?>" class="add-input" placeholder="..." required>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <td><button type="submit" name="add_record" class="btn-add">Save</button></td>
                </form>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($count > 0) {
                $result->data_seek(0);
                while($row = $result->fetch_assoc()) {
                    $is_editing = ($edit_id == $row['hospital_id']);
                    echo "<tr>";
                    if ($is_editing) {
                        // EDIT MODE ROW
                        echo "<form method='POST'>";
                        echo "<input type='hidden' name='update_id' value='".$row['hospital_id']."'>";
                        foreach($column_names as $col) {
                            $info = $metadata[$col];
                            echo "<td>";
                            if($info['Extra'] == 'auto_increment') {
                                echo htmlspecialchars($row[$col]);
                            } else {
                                echo "<input type='text' name='$col' value='".htmlspecialchars($row[$col])."' class='add-input'>";
                            }
                            echo "</td>";
                        }
                        echo "<td>
                                <button type='submit' name='update_record' style='background:#4CAF50; padding: 4px 8px; font-size: 0.8em;'>✔</button>
                                <a href='?".http_build_query(array_diff_key($_GET, ['edit_id'=>1]))."' class='btn-edit' style='background:#999;'>✕</a>
                              </td>";
                        echo "</form>";
                    } else {
                        // VIEW MODE ROW
                        foreach($row as $value) { 
                            echo "<td>" . htmlspecialchars($value) . "</td>"; 
                        }
                        echo "<td>
                                <a href='?".http_build_query(array_merge($_GET, ['edit_id'=>$row['hospital_id']]))."' class='btn-edit'>✎</a>
                                <form method='POST' onsubmit='return confirm(\"Are you sure?\");' style='display:inline;'>
                                    <input type='hidden' name='delete_id' value='" . $row['hospital_id'] . "'>
                                    <button type='submit' name='delete_record' style='background:#FF3B3F; padding: 4px 8px; font-size: 0.8em;'>✕</button>
                                </form>
                              </td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='100%'>No hospitals found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

