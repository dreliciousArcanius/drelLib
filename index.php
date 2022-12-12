<?php
if (!file_exists("dlib-config.php")) {
    header("Location: setup.php");
    die();
}
include("dlib-config.php");
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    // check if table coreLibrary and libraryUsers exists, if not redirect to setup.php
    $sql = "SELECT * FROM coreLibrary";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        die("Installation incomplete, or corrupt. Please do reinstall/troubleshoot accordingly");
    }
    $sql = "SELECT * FROM libraryUsers";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
    die("Installation incomplete, or corrupt. Please do reinstall/troubleshoot accordingly");
    }
}
?>






