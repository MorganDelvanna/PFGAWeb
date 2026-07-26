<?php
    
    require_once 'shared.php';

   
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
    $dbName = $_ENV['DB_NAME'] ?? null;
    $dbUser = $_ENV['DB_USER'] ?? null;
    $dbPass = $_ENV['DB_PASS'] ?? null;
     /*
    $dbHost = 'localhost:3306';
    $dbName = 'pfga_forum';
    $dbUser = 'root';
    $dbPass = '1q2w3e4r';
*/
    function sanitizeString($var)
    {
        global $mysqli;
        $var = strip_tags($var);
        $var = htmlentities($var);
        $var = stripslashes($var);
        return $mysqli->real_escape_string($var);
    }
    
    $error = $user = $pass = "";

    if (isset($_POST['user']))
    {
        if ($dbName && $dbUser) {
            $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if ($mysqli->connect_errno) {
                error_log('DB connect failed in email.php: ' . $mysqli->connect_error);
            } else {
                $user = sanitizeString($_POST['user']);
                $pass = sanitizeString($_POST['pass']);
                $role = 'admin';

                $salt1 = "qv&lm*";
                $salt2 = "pl@!x";
                $token = hash('ripemd128', "$salt1$pass$salt2");

                if ($user == "" || $pass == "")
                    $error = "Not all fields were entered<br />";
                else
                {
                    if($_POST["newUser"] == "yesWeAreAdding a new User"){
                        $stmt = $mysqli->prepare("INSERT INTO users(UserName, Password, Role) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $user, $token, $role);
                        $result = $stmt->execute();
                        if($result == true)
                        {
                            $error = "<span class='error'>Did't return anything</span><br><br>";
                        }
                        else
                        {
                            echo "<script>window.location = 'alogin.php'</script>";
                            die();
                        }

                    } else {
                        echo $token;
                        $stmt = $mysqli->prepare("SELECT UserName, Password, Role FROM users WHERE UserName=? AND Password=?");
                        $stmt->bind_param("ss", $user, $token);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows == 0)
                        {
                            $error = "<span class='error'>Username/Password invalid</span><br><br>";
                        }
                        else
                        {
                            $row = $result->fetch_array(MYSQLI_ASSOC);
                            $_SESSION['user'] = $row['UserName'];
                            $_SESSION['role'] = $row['Role'];
                            if ($_SESSION["role"] =="admin"){
                                echo "<script>window.location = 'email.php'</script>";
                            } else {
                                echo "<span class='error'>Invalid role</span><br><br>";
                                die();
                            }                  
                            
                        }
                    }  
                }                
            }
        }
        
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>PFGA Payment Successful</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css" />
        <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/pfga.css">
        <link rel="stylesheet" href="../css/menu.css" />   
        <link rel="icon" href="../images/pfgalogo.ico" type="image/icon type">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    </head>
    <body>
        <div><h3>Please enter your details to log in</h3>
            <form id="frmLogin" method='post' action='alogin.php'>
                <div class="row">
                    <div class="col-6">
                        <label for="user" class='fieldname'>UserName</label>
                        <input type='text' name='user' value =''>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label for="pass" class='fieldname'>Password</label>
                        <input type='password' name='pass' value=''>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <input type='submit' class='button' value='Login'>                
                        <input type="hidden" id="btnNewUser" name="newUser" value="anything">
                    </div>
                </div>                
            </form>
        </div>
        
        <script src="scripts/jquery-1.12.4.js"></script>
        <script>
            $(function(){
                $('#newUser').on("click", function(){
                    $('#btnNewUser').val("true");
                    $('#frmLogin').trigger("submit");
                });
            });        
        </script>
    </body>
</html>