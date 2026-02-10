<?php
    require_once 'header.php';
    
    $error = $user = $pass = "";

    if (isset($_POST['user']))
    {
        $user = sanitizeString($_POST['user']);
        $pass = sanitizeString($_POST['pass']);

        $salt1 = "qv&lm*";
        $salt2 = "pl@!x";
        $token = hash('ripemd128', "$salt1$pass$salt2");

        if ($user == "" || $pass == "")
            $error = "Not all fields were entered<br />";
        else
        {
            if($_POST["newUser"] == "yesWeAreAdding a new User"){
                $result = queryMySQL("INSERT INTO users(UserName, Password, Role) VALUES ('$user', '$token', 'news')");
                if($result == true)
                {
                    $error = "<span class='error'>Did't return anything</span><br><br>";
                }
                else
                {
                    echo "<script>window.location = 'login.php'</script>";
                    die();
                }

            } else {
                $result = queryMySQL("SELECT UserName, Password, Role FROM users WHERE UserName='$user' AND Password='$token'");

                if ($result->num_rows == 0)
                {
                    $error = "<span class='error'>Username/Password invalid</span><br><br>";
                }
                else
                {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    $_SESSION['user'] = $row['UserName'];
                    $_SESSION['role'] = $row['Role'];
                    if ($_SESSION['role'] == "calendar"){
                        echo "<script>window.location = 'eventList.php'</script>";
                    } else if ($_SESSION["role"] =="news"){
                        echo "<script>window.location = 'newsList.php'</script>";
                    } else {
                        echo "<span class='error'>Invalid role</span><br><br>";
                    }                    
                    die();
                }
            }            
        }
    }
?>
<!DOCTYPE html>
<html>
    <head></head>
    <body>
        <div><h3>Please enter your details to log in</h3>
            <form id="frmLogin" method='post' action='login.php'>
                <span class='fieldname'>UserName</span>
                <input type='text' name='user' value =''><br>
                <span class='fieldname'>Password</span>
                <input type='password' name='pass' value=''>
                <br>
                
                <input type='submit' class='button' value='Login'>                
                <input type="hidden" id="btnNewUser" name="newUser" value="anything">
            </form><br>
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