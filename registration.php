<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creating User</title>

</head>


<body>

<?php

//include "dbConnection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $surname = isset($_POST["surname"]) ? trim($_POST["surname"]) : "";
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
    $contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : "";
    $physAdd = isset($_POST["addr"]) ? trim($_POST["addr"]) : "";
    $role = isset($_POST["userrole"]) ? ($roleMap[$_POST["userrole"]] ?? "") : "";
    $roleMap = ["1" => "Community Member", "2" => "Ward councillor", "3" => "Municipal Officer", "4" => "Systems Administrator"];
    $password = isset($_POST["pword"]) ? trim($_POST["pword"]) : "";

    //$ward = isset($_POST["ward"]) ? trim($_POST["ward"]) :"";
    $hash_pword = password_hash($password, PASSWORD_DEFAULT);

    //$stmt = $conn->prepare("INSERT INTO accounts(username, password, phone_number, name, surname, role, is_registrered, active_status)
        VALUES (?,?,?,?,?,?,?,?)");

    if ($stmt === false) {
        die("Prepare failed" . $conn->error);
    }

    $stmt->bind_param("i" . str_repeat("s", 9), $idnumber, $surname, $firstname, $gender, $userrole, $username, $hash_pword, $physAdd, $contact, $email, $ward);

    if ($stmt->execute()) {
        echo "<p>Record for $firstname $surname successfully created. <p>";
        echo "<a href='signin.html'>Go to Login Page </a>";
    } else {
        echo "<a href='signup.html'>Back to Signup </a><br>";
        die("Record could not be created" . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>

</body>
</html>
