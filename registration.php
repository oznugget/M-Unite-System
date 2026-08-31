<?php

include "dbConnection.php";

// Function to display error messages in a styled format
function showError($message) {
    die("
    <div style='background: #DCE6F2; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif;'>
        <div style='background: #ffffff; padding: 30px; border-radius: 4px; border-top: 5px solid #d9534f; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 450px; text-align: center;'>
            <h3 style='color: #d9534f; margin: 0 0 12px 0; font-size: 1.3rem;'>Registration Failed</h3>
            <p style='color: #444444; font-size: 14px; line-height: 1.5; margin-bottom: 20px;'>{$message}</p>
            <a href='signup.html' style='display: inline-block; padding: 10px 20px; background: #0E2841; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 2px; font-size: 13px;'>← Back to Signup</a>
        </div>
    </div>
    ");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $surname = isset($_POST["surname"]) ? trim($_POST["surname"]) : "";
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
    $contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : "";
    $physAdd = isset($_POST["addr"]) ? trim($_POST["addr"]) : "";
    $roleMap = ["1" => "Community Member", "2" => "Ward councillor", "3" => "Municipal Officer", "4" => "System Admin"];
    $role = isset($_POST["userrole"]) ? ($roleMap[$_POST["userrole"]] ?? "") : "";
    $password = isset($_POST["pword"]) ? trim($_POST["pword"]) : "";
    $hash_pword = password_hash($password, PASSWORD_DEFAULT);
    $division = !empty($_POST["division"]) ? trim($_POST["division"]) : null;

   // Standard required fields
    if (empty($firstname) || empty($surname) || empty($email) || empty($contact) || empty($role) || empty($password)) {
        die("All required fields must be completed.");
    }

    // Require physical address  if user is a Community Member
    if ($role === "Community Member" && empty($physAdd)) {
        die("Physical address is required for Community Members.");
    }

    if($role != "Community Member" && substr((strrchr($email, "@")), 1) != "makana.gov.za") {
        header("Location: createacc.php?error=email_domain");
        exit();
    }

    //for splitting the physical address into street number, street name and suburb
    $lat           = $_POST["lat"] ?? "";
    $lon           = $_POST["lon"] ?? "";
    $street_number = $_POST["street_number"] ?? "";
    $street_name   = $_POST["street_name"] ?? "";
    $suburb        = $_POST["suburb"] ?? "";


    //Determining ward id via Mapit API using the provided latitude and longitude- default being 1
    $ward_id = 1; 
        if (!empty($lat) && !empty($lon)) {
            $mapit_url = "https://mapit.code4sa.org/point/4326/{$lon},{$lat}?type=WD";
            $opts = ["http" => ["header" => "User-Agent: MakhandaWardApp/1.0\r\n", "timeout" => 3]];
            $res = @file_get_contents($mapit_url, false, stream_context_create($opts));
            
            if ($res) {
                $ward_data = json_decode($res, true);
                if (!empty($ward_data)) {
                    $first_ward = reset($ward_data);
                    if (isset($first_ward['name'])) {
                        preg_match('/\d+/', $first_ward['name'], $matches);
                        if (isset($matches[0])) {
                            $ward_id = (int)$matches[0];
                        }
                    }
                }
            }
        }


    $username = $email; // Using email as username

    //Filling in accounts table
    $stmt = $conn->prepare("INSERT INTO accounts(username, password, phone_number, name, surname, role, is_registered, active_status)
        VALUES (?,?,?,?,?,?,?,?)");

    if ($stmt === false) {
        showError("Prepare failed" . $conn->error);
    }else{
        $is_registered = 1;
        $active_status = 1;
    }

    $stmt->bind_param("ssssssii", $username, $hash_pword, $contact, $firstname, $surname, $role, $is_registered, $active_status);
    
    if ($stmt->execute()) {



    //Filling in community member table
    if ($role === "Community Member") {
        $stmt2 = $conn->prepare("INSERT INTO community_member(username, ward_id, street_number, street_name, suburb) 
            VALUES (?,?,?,?,?)");

    if ($stmt2 === false) {
        showError("Prepare failed" . $conn->error);
    }
    
    if($stmt2){
        $stmt2->bind_param("sisss", $username, $ward_id, $street_number, $street_name, $suburb);
        $stmt2->execute();
        $stmt2->close();
    }

    //Filling in ward councillor table
    } else if ($role === "Ward councillor") {
        $stmt3 = $conn->prepare("INSERT INTO ward_councillors(username, ward_id) VALUES (?,?)");

        if ($stmt3 === false) {
            showError("Prepare failed" . $conn->error);
        }
        $stmt3->bind_param("si", $username, $ward_id);
        $stmt3->execute();
        $stmt3->close();
    

    //Filling in municipal officer table
    }else if ($role === "Municipal Officer") {
        $stmt4 = $conn->prepare("INSERT INTO municipal_officers(username, division) VALUES (?,?)");

        if( $stmt4 === false) {
            showError("Prepare failed" . $conn->error);
        }
        $stmt4->bind_param("ss", $username, $division);
        $stmt4->execute();
        $stmt4->close();

    } else if ($role === "Systems Administrator") {
        $stmt5 = $conn->prepare("INSERT INTO system_admins(username) VALUES (?)");

        if ($stmt5 === false) {
            showError("Prepare failed" . $conn->error);
        }
        $stmt5->bind_param("s", $username);
        $stmt5->execute();
        $stmt5->close();
    }


       header("Location: signin.html?registration=success");
        exit();
    } else {
        echo "<a href='signup.html'>Back to Signup </a><br>";
        showError("Record could not be created" . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>

