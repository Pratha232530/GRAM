<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = 'localhost';
$db_name = 'gram_sahayak';
$username = 'root';
$password = '12345'; // Keep empty for XAMPP

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

$res = ["success" => false];

try {
    switch($action) {
        
        // --- AUTHENTICATION (PLAIN TEXT) ---
        case 'login':
            $searchKey = (isset($data['username']) && $data['username'] === 'admin') ? 'admin' : $data['email'];

            $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
            $stmt->execute([$searchKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // --- DEBUGGING START ---
            if (!$user) {
                $res = ["success" => false, "message" => "Debug: User not found in DB. Searched for: " . $searchKey];
            } elseif ($data['password'] != $user['password']) {
                $res = [
                    "success" => false, 
                    "message" => "Debug: Password Mismatch.",
                    "debug_info" => [
                        "You sent" => $data['password'],
                        "Database has" => $user['password']
                    ]
                ];
            } 
            // --- DEBUGGING END ---
            
            else {
                // Success
                $res = [
                    "success" => true, 
                    "user" => [
                        "id" => $user['user_id'], 
                        "name" => $user['user_name'], 
                        "email" => $user['user_email'], 
                        "type" => $user['role']
                    ]
                ];
            }
            break;

        case 'register_user':
            $check = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
            $check->execute([$data['email']]);
            
            if($check->rowCount() > 0) {
                $res = ["success" => false, "message" => "Email already exists"];
            } else {
                // CHANGED: No password_hash. Saving plain text.
                $plainPassword = $data['password']; 
                
                $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, password, role) VALUES (?, ?, ?, 'user')");
                
                if($stmt->execute([$data['name'], $data['email'], $plainPassword])) {
                    $newId = $conn->lastInsertId();
                    $res = [
                        "success" => true, 
                        "user" => [
                            "id" => $newId, 
                            "name" => $data['name'], 
                            "email" => $data['email'], 
                            "type" => "user"
                        ]
                    ];
                }
            }
            break;

        // --- ALL OTHER ACTIONS REMAIN THE SAME ---
        case 'get_dashboard':
            $tables = ['users' => 'allUsers', 'services' => 'services', 'meetings' => 'meetings', 'work_reports' => 'workReports', 'waste_requests' => 'wasteRequests', 'queries' => 'queries', 'incidents' => 'incidents'];
            $dbData = [];
            foreach($tables as $sqlTbl => $jsKey) {
                if($sqlTbl === 'users') {
                    $q = $conn->query("SELECT user_id, user_name, user_email, role, created_at FROM users ORDER BY user_id DESC");
                } else {
                    $q = $conn->query("SELECT * FROM $sqlTbl ORDER BY id DESC"); 
                }
                $dbData[$jsKey] = $q->fetchAll(PDO::FETCH_ASSOC);
            }
            $res = ["success" => true, "data" => $dbData];
            break;

        case 'add_waste':
            $stmt = $conn->prepare("INSERT INTO waste_requests (user_id, user_name, location) VALUES (?, ?, ?)");
            $stmt->execute([$data['user_id'], $data['user_name'], $data['location']]);
            $res = ["success" => true];
            break;

        case 'update_waste_status':
            $stmt = $conn->prepare("UPDATE waste_requests SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            $res = ["success" => true];
            break;

        case 'delete_waste':
            $stmt = $conn->prepare("DELETE FROM waste_requests WHERE id = ?");
            $stmt->execute([$data['id']]);
            $res = ["success" => true];
            break;

        case 'submit_query':
            $stmt = $conn->prepare("INSERT INTO queries (user_id, user_name, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['user_id'], $data['user_name'], $data['subject'], $data['message']]);
            $res = ["success" => true];
            break;

        case 'update_query_status':
            $stmt = $conn->prepare("UPDATE queries SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            $res = ["success" => true];
            break;

        case 'submit_incident':
            $stmt = $conn->prepare("INSERT INTO incidents (user_id, user_name, incident_type, location, details) VALUES (?, ?, ?, ?, ?)");
            $u_id = ($data['anonymous']) ? null : $data['user_id'];
            $u_name = ($data['anonymous']) ? 'Anonymous' : $data['user_name'];
            $stmt->execute([$u_id, $u_name, $data['incident_type'], $data['location'], $data['details']]);
            $res = ["success" => true];
            break;

        case 'update_incident_status':
            $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            $res = ["success" => true];
            break;
            
        case 'add_work':
            $stmt = $conn->prepare("INSERT INTO work_reports (title, status) VALUES (?, ?)");
            $stmt->execute([$data['title'], $data['status']]);
            $res = ["success" => true];
            break;

        case 'delete_work':
            $stmt = $conn->prepare("DELETE FROM work_reports WHERE id = ?");
            $stmt->execute([$data['id']]);
            $res = ["success" => true];
            break;

        case 'add_meeting':
            $stmt = $conn->prepare("INSERT INTO meetings (location, date, time, purpose) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['location'], $data['date'], $data['time'], $data['purpose']]);
            $res = ["success" => true];
            break;

        case 'delete_meeting':
            $stmt = $conn->prepare("DELETE FROM meetings WHERE id = ?");
            $stmt->execute([$data['id']]);
            $res = ["success" => true];
            break;

        case 'delete_user':
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$data['id']]);
            $res = ["success" => true];
            break;

        case 'submit_application':
            $stmt = $conn->prepare("INSERT INTO services (user_id, user_name, service_type, details, document) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['user_id'], $data['user_name'], $data['service_type'], $data['details'], $data['documentName']]);
            $res = ["success" => true];
            break;

        case 'update_application_status':
            $stmt = $conn->prepare("UPDATE services SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            $res = ["success" => true];
            break;
            
        default:
            $res = ["success" => false, "message" => "Invalid Action"];
    }
} catch (Exception $e) {
    $res = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($res);
?>