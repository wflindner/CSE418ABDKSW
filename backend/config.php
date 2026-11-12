<?php

// Connect to MySQL locally when this backend is deployed on cattle.
$host = "localhost";
$username = "wflindne";
$password = "50529150";
$database = "cse442_2026_spring_team_c_db";

// Return settings for the shared PDO connection in src/db.php.
return [
    'db_host' => $host,
    'db_user' => $username,
    'db_pass' => $password,
    'db_name' => $database,
];
