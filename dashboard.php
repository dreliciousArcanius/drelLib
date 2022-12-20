<?php
session_start();
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



// get data of available books, which is the sum of Book - borrowBook, and to match both use isbn
$sql = "SELECT * FROM book";
$result = mysqli_query($conn, $sql);
$books = mysqli_fetch_all($result, MYSQLI_ASSOC);

$sql = "SELECT * FROM borrowBook";
$result = mysqli_query($conn, $sql);
$borrowBook = mysqli_fetch_all($result, MYSQLI_ASSOC);

// delete array if borrowBook date returned is filled
foreach ($borrowBook as $key => $value) {
    if ($value['dateReturned'] != null) {
        unset($borrowBook[$key]);
    }
}

// get the sum of books available by subtracting borrowBook from books and only show how much books are available in number

for ($i = 0; $i < count($books); $i++) {
    $books[$i]['available'] = $books[$i]['quantity'];
    for ($j = 0; $j < count($borrowBook); $j++) {
        if ($books[$i]['isbn'] == $borrowBook[$j]['isbn']) {
            $books[$i]['available'] = $books[$i]['available'] - 1;
        }
    }
}

// get 3 top borrowed books data, it's counted by how much time it's been borrowed in borrowBook and after sorted with top 3 most borrowed, it'll match the isbn data with book and show the title

// using MYSQL query to Group by isbn and count with having 
$sql = "SELECT isbn, COUNT(isbn) AS count FROM borrowBook GROUP BY isbn HAVING COUNT(isbn) > 0 ORDER BY count DESC LIMIT 3";
$result = mysqli_query($conn, $sql);
$topbooks = mysqli_fetch_all($result, MYSQLI_ASSOC);
$bookTop = array();
// search the count
for ($z = 0; $z < count($topbooks); $z++) {
    $sql = "SELECT * FROM book WHERE isbn = '" . $topbooks[$z]['isbn'] . "'";
    $result = mysqli_query($conn, $sql);
    $topbooks[$z]['title'] = mysqli_fetch_assoc($result)['title'];
    $topbooks[$z]['count'] = $topbooks[$z]['count'];
    $result = mysqli_query($conn, $sql);
    $bookTop[$z] = mysqli_fetch_assoc($result);

}


// get the count of borrowIncident, with the date range of 30 days on the date
$sql = "SELECT * FROM borrowIncident WHERE date BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()";
$result = mysqli_query($conn, $sql);
$incidents = mysqli_fetch_all($result, MYSQLI_ASSOC);

for ($incidentnum = 0; $incidentnum < count($incidents); $incidentnum++) {
}

// get the count of active library user, with the range of 30 days on the date match email from borrowBook and libraryUsers
$sql = "SELECT * FROM borrowBook WHERE dateBorrowed BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()"; 
$result = mysqli_query($conn, $sql);
$booksBorrowed = mysqli_fetch_all($result, MYSQLI_ASSOC);

$sql = "SELECT * FROM libraryUsers WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

for ($active = 0; $active < count($users); $active++) {
    for ($borrowedBooks = 0; $borrowedBooks < count($booksBorrowed); $borrowedBooks++) {
        // if unmatched then active--
        if ($users[$active]['email'] != $booksBorrowed[$borrowedBooks]['email']) {
            $active--;
        }
    }
}



// check if user is logged in
if (isset($_SESSION['email'])) {
    // check if user is admin
    $sql = "SELECT * FROM libraryUsers WHERE email = '" . $_SESSION['email'] . "'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if ($row['role'] == "admin") {
            // user is admin
            // check if user is logged in
            if (isset($_SESSION['email'])) {
                // check if user is admin
                $sql = "SELECT * FROM libraryUsers WHERE email = '" . $_SESSION['email'] . "'";
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);
                    
                    if ($row['role'] == "admin") {
                        $sql = "SELECT * FROM coreLibrary";
                        $result = mysqli_query($conn, $sql);
                        $coreLib = mysqli_fetch_assoc($result);
                        
                        $libraryName = $coreLib['libraryName'];
                        $libraryLogo = $coreLib['libraryLogo'];
                        $libraryAddress = $coreLib['libraryAddress'];

                        ?>
                        <!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Dashboard - <?php echo $libraryName; ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="css/dashboard.css">
        <script src="depend/qrcode/html5-qrcode.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v6.0.0-beta3/css/all.css">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Lora:wght@500&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="sidebar-pc" style="height: 100vh; width: 30vh; background-color: rgb(255, 255, 255);">
                <ul style="text-decoration: none; list-style: none; ">
                    <li>
                        <!--Logo-->
                        <a href="dashboard.html">
                            <img src="<?php echo $coreLib['libraryLogo'];?>" width="75%">
                        </a>
                    </li>
                    <li>
                        <!--Account Status, Either Library Admin or User-->
                        <a href="dashboard.html">
                            <div class="tab">
                                <button style="background-color: #fff; width: 50%; color: #0779E4;">Librarian</button>
                                <button style="text-align: center; width: 50%;">User</button>
                              </div>
                        </a>
                    </li>
                    <table style="width: 100%; border-spacing: 0 2vh;">
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; font-size: 15px; width: 0; "><i class="fa-regular fa-grid-2"></i></td>
                            <td style="border-right: 3px solid #0779E4;;">
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: #0779E4; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Dashboard
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-regular fa-wrench"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Library Configuration
                                </a>
                            </td>
                        </tr>
                    </table>
                    
                    <table style="width: 100%; border-spacing: 0 2vh;">
                        <p style="font-family: Poppins; color: rgb(190, 185, 150); margin: 0; margin-top: 10px; ">Books</p>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; font-size: 15px; width: 0; "><i class="fa-light fa-books"></i></td>
                            <td style="">
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Manage Books
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-solid fa-book-open-reader"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Borrow Books
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-solid fa-book-bookmark"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Past-due Books
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-sharp fa-solid fa-circle-exclamation"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Incidents
                                </a>
                            </td>
                        </tr>
                    </table>
                    <table style="width: 100%; border-spacing: 0 2vh;">
                        <p style="font-family: Poppins; color: rgb(190, 185, 150); margin: 0; margin-top: 10px; ">Reports</p>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; font-size: 15px; width: 0; "><i class="fa-solid fa-book-bookmark"></i></td>
                            <td style="">
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Books Report
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-regular fa-user"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Users Report
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-solid fa-flag"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Incidents Report
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle; width: 0; font-size: 15px;"><i class="fa-thin fa-file-chart-column"></i></td>
                            <td>
                                                        <!--Dashboard-->
                                <a style="font-family: Poppins; vertical-align: middle; color: black; font-size: 15px; text-decoration: none;" href="dashboard.html">
                                    Book Borrowing Report
                                </a>
                            </td>
                        </tr>
                    </table>
                </ul>

            </div>
                <div class="content">
                    <div class="menu-map">
                        <p style="font-size: 2vh; font-family: 'Montserrat'; color: rgb(98, 98, 100);">Library Dashboard > <b style="font-family: 'Poppins';">Dashboard</b></p>
                        <div class="sidebar-mobile">
                            <i onclick="toggleSidebar()" style="font-size: 3vh;"  class="fa-regular fa-grid-2"></i>
                        </div>
                    </div>
                    
                    <div class="flexbox">
                       <div class="card">
                        <div class="card-column">
                            <i style="display: block; font-size: 2vh;" class="fa-light fa-books"></i>
                        </div>
                        <div class="card-column">
                            <p style="font-size: 3vh; font-family: 'Poppins'; color: black; "><b><?php echo $i; ?></b></p>
                            <p style="font-size: 1.5vh; font-family: 'Poppins'; color: rgb(98, 98, 100);"><b>Available Books</b></p>
                        </div>
                       </div>
                       <div class="card">
                        <div class="card-column">
                            <i style="display: block;" class="fa-sharp fa-solid fa-circle-exclamation"></i>
                        </div>
                        <div class="card-column">
                            <p style="font-size: 3vh; font-family: 'Poppins'; color: black;"><b><?php echo $incidentnum;  ?></b></p>
                            <p style="font-size: 1.5vh; font-family: 'Poppins'; color: rgb(98, 98, 100);">Incidents</p>
                        </div>
                       </div>
                       <div class="card">
                        <div class="card-column">
                            <i style="display: block;" class="fa-regular fa-user"></i>
                        </div>
                        <div class="card-column">

                            <p style="font-size: 3vh; font-family: 'Poppins'; color: black;"><b><?php echo $active ?></b></p>
                            <p style="font-size: 1.5vh; font-family: 'Poppins'; color: rgb(98, 98, 100);">Active Users</p>
                        </div>
                       </div>
                    </div>
                    
                    <div class="main-content">
                          <h1 style="font-family: Roboto;">Dashboard</h1>
                          <div class="main-contentfix">
                            <div id="reader-container" style="margin-right: 50px;">
                                <p style="font-family: 'Poppins'; font-size: 2vh; color: black; margin-bottom: 10px;">Scan Borrowing Book</p>
                                <div id="reader" style="width: 100%"></div>
                                </div>
                            <div id="book-info">
                            <div class="card">
                                <div class="book-info">
                                    <p  class="book-icon">
                                        <img src="img/Screenshot 2022-12-15 at 2.24.41 PM.png" class="book-icon">
                                    </p>
                                    <div class="fill">
                                        <p style="font-family: 'Poppins'; font-size: 2vh; color: black;">Book Title</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">Author</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">Publisher</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">ISBN</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">Category</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">Status</p>
                                        <p style="font-family: 'Poppins'; font-size: 1.5vh; color: rgb(98, 98, 100);">Borrower</p>
                                    </div>
                                    <div class="card-column">
                                        <button style="align-items: center; justify-content: center; justify-items: center;  font-family: 'Poppins'; font-size: 1.5vh; color: white; background-color: #2ECC71; border: none; border-radius: 5px; padding: 10px 20px;">Return</button>
                                    </div>
                                </div>
                            </div>
                          </div>
                          <div class="top-books">
                            <p style="font-family: 'Poppins'; font-size: 2vh; color: black; margin-bottom: 10px;">Top Borrowed Books</p>
                            <div class="top-books-container">
                                <div class="card" id="top-borrowed">
                                        <?php 
                                        for ($m = 0; $m < count($bookTop); $m++ ) {
                                            echo '<div class="card-row">';
                                            echo "<img src='" . $bookTop[$m]['image']  ."' id='imagemain' width='100vh'>";
                                            echo '<div class="card-column" style="width: 100%;">';
                                            echo '<p id="smale">' . $bookTop[$m]["title"] . '</p>';
                                            echo "<p id='smile'>" . $bookTop[$m]["author"] . "</p>";
                            echo "<p id='smile' >" . $topbooks[1]['count'] . " (x) Borrowed</p>";
                                            echo '</div>';
                                            echo '<h1>' . $m + 1 . '</h1>';
                                            echo '</div>';
                                        }
                                        ?>                      
                          </div>
                          </div>


                          <script>
                            function onScanSuccess(decodedText, decodedResult) {
  // handle the scanned code as you like, for example:
  console.log(`Code matched = ${decodedText}`, decodedResult);
  // hide div id reader 
    html5QrcodeScanner.clear();
    document.getElementById("reader").style.display = "none";
    document.getElementById("reader-container").style.display = "none";
    // show div id book-info
    document.getElementById("book-info").style.display = "block";
}

function onScanFailure(error) {
  // handle scan failure, usually better to ignore and keep scanning.
  // for example:
  console.warn(`Code scan error = ${error}`);
}

let html5QrcodeScanner = new Html5QrcodeScanner(
  "reader",
  { fps: 10, qrbox: {width: 250, height: 250} },
  /* verbose= */ false);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                          </script>
                    </div>


                    <script>
                        function toggleSidebar(){
                            var sidebar = document.querySelector('.sidebar-pc');
                            if(sidebar.style.display == 'block'){
                                sidebar.style.display = 'none';
                            }else{
                                sidebar.style.display = 'block';
                            }
                        }
                    </script>
                </div>
        </div>

        <script src="" async defer></script>
    </body>
</html>








                        <?php
                    } else {
                        // dashboard not admin
                        echo "";
                    }
                }
            }
        }
    }
}

?>
