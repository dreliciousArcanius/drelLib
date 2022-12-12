<?php
$mysql_ip = "";
$mysql_user = "";
$mysql_pass = "";
$mysql_db = "";
$warn = "";
$phase = 1;
if (file_exists("dlib-config.php")) {
    include("dlib-config.php");
    $i = count(file("dlib-config.php"));
    if ($i > 5) {
        header("Location: index.php");
        die("Installation complete. Please delete setup.php");
    }
} 

if(isset($_POST['checkMYSQL'])) 
{
    $mysql_ip = $_POST['database_ip'];
    $mysql_user = $_POST['database_user'];
    $mysql_pass = $_POST['database_pass'];
    $mysql_db = $_POST['database_name'];
    $conn = mysqli_connect($mysql_ip, $mysql_user, $mysql_pass, $mysql_db);
    if (!$conn) {
        $warn = "Connection failed: " . mysqli_connect_error();
    } else {
        $warn = "Connection successful!";
        $phase = 2;
        $myfile = fopen("dlib-config.php", "w") or die("Unable to open file!");
        $txt = "<?php\n";
        fwrite($myfile, $txt);
        $txt = "define('DB_HOST', '" . $mysql_ip . "');\n";
        fwrite($myfile, $txt);
        $txt = "define('DB_USER', '" . $mysql_user . "');\n";
        fwrite($myfile, $txt);
        $txt = "define('DB_PASS', '" . $mysql_pass . "');\n";
        fwrite($myfile, $txt);
        $txt = "define('DB_NAME', '" . $mysql_db . "');\n";
        fwrite($myfile, $txt);
        fclose($myfile);
        mysqli_close($conn);
    }
}

if(isset($_POST['finishweb'])) {
    include('dlib-config.php');
    $webaddress = $_POST['webaddress'];
    $myfile = fopen("dlib-config.php", "a") or die("Unable to open file!");
    $txt = "define('WEB_ADDRESS', '" . $webaddress . "');\n";
    fwrite($myfile, $txt);
    $txt = "?>";
    fwrite($myfile, $txt);
    fclose($myfile);
    $file = $_FILES['logo'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_dest = "img/" . $file_name;
    move_uploaded_file($file_tmp, $file_dest);
    $libraryname = $_POST['libraryname'];
    $libraryaddress = $_POST['libraryaddress'];
    $adminemail = $_POST['adminemail'];
    $adminpass = $_POST['adminpassword'];
    $adminpass = md5($adminpass);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // if table coreLibrary doesn't exist, then create table coreLibrary 
    $sql = "CREATE TABLE IF NOT EXISTS coreLibrary (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libraryName VARCHAR(255) NOT NULL,
        libraryAddress VARCHAR(255) NOT NULL,
        libraryLogo VARCHAR(255) NOT NULL
    )";
    mysqli_query($conn, $sql);

    // create table libraryUsers if doesnt exist with roles
    $sql = "CREATE TABLE IF NOT EXISTS libraryUsers (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        role VARCHAR(255) NOT NULL
    )";
    mysqli_query($conn, $sql);

    // create table book if not exist which consists of title, image, quantity, publishDate, author, publisher, isbn, language, category, dateAdded, id
    $sql = "CREATE TABLE IF NOT EXISTS book (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image VARCHAR(255) NOT NULL,
        quantity INT(6) NOT NULL,
        publishDate VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        publisher VARCHAR(255) NOT NULL,
        isbn VARCHAR(255) NOT NULL,
        language VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        dateAdded DATE NOT NULL
    )";

    mysqli_query($conn, $sql);

    // create table borrowBook if not exist which consists of email, bookSecretCode, dateBorrowed, dateDue, bookID, isbn, dateReturned, borrowID, id
    $sql = "CREATE TABLE IF NOT EXISTS borrowBook (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        bookSecretCode VARCHAR(255) NOT NULL,
        dateBorrowed DATE NOT NULL,
        dateDue DATE NOT NULL,
        bookID INT(6) NOT NULL,
        isbn VARCHAR(255) NOT NULL,
        dateReturned DATE NOT NULL,
        borrowID INT(6) NOT NULL
    )";

    mysqli_query($conn, $sql);

    // create table borrowIncident if not exist which consists of date, incidentType, incidentDescription, penalty, isbn, bookID, borrowID, id
    $sql = "CREATE TABLE IF NOT EXISTS borrowIncident (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        incidentType VARCHAR(255) NOT NULL,
        incidentDescription VARCHAR(255) NOT NULL,
        penalty INT(6) NOT NULL,
        isbn VARCHAR(255) NOT NULL,
        bookID INT(6) NOT NULL,
        borrowID INT(6) NOT NULL
    )";

    mysqli_query($conn, $sql);

    // create table borrowerCredibility if not exist which consists of email, timesBorrowed, timesOntime, timesLate, timesLost, timesDamaged, id
    $sql = "CREATE TABLE IF NOT EXISTS borrowerCredibility (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        timesBorrowed INT(6) NOT NULL,
        timesOntime INT(6) NOT NULL,
        timesLate INT(6) NOT NULL,
        timesLost INT(6) NOT NULL,
        timesDamaged INT(6) NOT NULL
    )";


    // enter data into coreLibrary
    $sql = "INSERT INTO coreLibrary (libraryName, libraryAddress, libraryLogo) VALUES ('$libraryname', '$libraryaddress', '$file_dest')";
    mysqli_query($conn, $sql);

    // enter data into libraryUsers
    $sql = "INSERT INTO libraryUsers (username, password, email, role) VALUES ('admin', '$adminpass', '$adminemail', 'admin')";
    mysqli_query($conn, $sql);
    mysqli_close($conn);
    header("Location: index.php");
}

/*
if ($_FILES['file']['size'] > $max_file_size) {
    // File is too large
    // Do something (e.g. send an error message)
} else {
    // File is within the allowed size
    // Do something (e.g. save the file to the server)
}
*/
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>drelLib - Setup Page</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/css/setup.css">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Lora:wght@500&display=swap" rel="stylesheet">
    </head>
    <body style="margin: 0;">
        <div class="center">
            <div class="box" id="form1">
                <div class="header">
                    <h1 style="font-family: 'Lora', serif;">drelLib</h1>
                    <h3 style="font-family: 'Lora', serif;">Connect your db.<hr></h3>
                </div>
                <div class="box-content">
                    <?php
                    if (!$warn == "") {
                        echo "<p style='font-family: 'Montserrat', sans-serif; color: red;'>" . $warn . "</p>";
                    }
                    ?>
                    <h2 style="margin: 0px; font-family: 'Poppins', sans-serif;">Database Information</h2>
                    <p style="margin: 0px; font-family: 'Montserrat', sans-serif;">MySQL Database</p>
                    <hr>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <table>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Database IP</label></td>
                                <td><input required name="database_ip" style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Database IP">
                                </td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Database User</label></td>
                                <td><input required name="database_user" style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Database User"></td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Database Name</label></td>
                                <td><input required name="database_name" style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Database Name"></td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Database Password</label></td>
                                <td><input type="password" required name="database_pass" style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Database Password"></td>
                            </tr>
                        </table>
                        <div style="display: flex; justify-content: center; align-items: center; margin-top: 2vh;">
                            <button type="submit" name="checkMYSQL" style="height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000;">Next</button>
                        </div>
                       
                       
                    </form>
                </div>
            </div>
                    <div class="box" id="form2" style="display: none;">
                    <div class="header">
                    <h1 style="font-family: 'Lora', serif;">drelLib</h1>
                    <h3 style="font-family: 'Lora', serif;">Customize the Lib<hr></h3>
                </div>
                <div class="box-content">
                    <h2 style="margin: 0px; font-family: 'Poppins', sans-serif;">Set-the-Library.</h2>
                    <p style="margin: 0px; font-family: 'Montserrat', sans-serif;">custom-o-custom!</p>
                    <hr>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Web Address</label></td>
                                <td>
                                    <input name="webaddress" required style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Web Address">
                                </td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px; text-align: center;">Library Logo</label><p style="margin: 0; padding:0; font-size: small;">512x512px</p></td>
                                <td>
                                    <input name="logo" type="file"  accept=".png, .jpeg, .jpg, image/jpeg" style="width: 100%; height: 30px;"></td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Library Name</label></td>
                                <td><input name="libraryname" required style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Library Name">
                                </td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Library Address</label></td>
                                <td><input name="libraryaddress" required style="width: 100%; height: 100px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" placeholder="Library Address"></td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Admin E-mail</label></td>
                                <td><input name="adminemail" required style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" type="email" placeholder="E-mail"></td>
                            </tr>
                            <tr>
                                <td><label style="font-family: 'Monserrat', sans-serif; margin-right: 10px;">Password</label></td>
                                <td><input name="adminpassword" required style="width: 100%; height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000; text-indent: 10px;" type="password" placeholder="Password"></td>
                            </tr>
                        </table>
                        <div style="display: flex; justify-content: center; align-items: center; margin-top: 2vh;">
                            <button name="finishweb" type="submit" style="height: 30px; font-family: 'Montserrat', sans-serif;  background-color: #eaeaec; border-radius: 5px; border: 1px solid #eaeaec; color:#000000;">Finish Setup</button>
                        </div>
                       
                       
                    </form>
                </div>
            </div>
        </div>

        <?php
        if ($phase > 1 ) {
            echo "<script>document.getElementById('form1').style.display = 'none';</script>";
            echo "<script>document.getElementById('form2').style.display = 'block';</script>";
        }
    ?>
    </body>
</html>





<?php
?>