<?php
session_start();

// Khai báo các lỗi và giá trị mặc định
$nameErr = $emailErr = $genderErr = $addressErr = $icErr = $contactErr = $usernameErr = $passwordErr = "";
$name = $email = $gender = $address = $ic = $contact = $uname = $upassword = "";
$cID = "";

$oUserName = $oPassword = $oName = $oIC = $oEmail = $oPhone = $oAddress = "";

// Kết nối tới cơ sở dữ liệu bằng PDO
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=bookstore", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy thông tin người dùng hiện tại
    $stmt = $conn->prepare("SELECT users.UserName, users.Password, customer.CustomerName, customer.CustomerIC, 
                            customer.CustomerEmail, customer.CustomerPhone, customer.CustomerGender, customer.CustomerAddress 
                            FROM users, customer 
                            WHERE users.UserID = customer.UserID AND users.UserID = :userID");
    $stmt->bindParam(':userID', $_SESSION['id']);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $oUserName = $row['UserName'];
    $oPassword = $row['Password'];
    $oName = $row['CustomerName'];
    $oIC = $row['CustomerIC'];
    $oEmail = $row['CustomerEmail'];
    $oPhone = $row['CustomerPhone'];
    $oAddress = $row['CustomerAddress'];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Kiểm tra dữ liệu form khi người dùng submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra và xử lý thông tin từ form
    if (empty($_POST["name"])) {
        $nameErr = "Please enter your name";
    } else {
        if (!preg_match("/^[a-zA-Z ]*$/", $_POST["name"])) {
            $nameErr = "Only letters and white space allowed";
        } else {
            $name = test_input($_POST["name"]);
        }
    }

    if (empty($_POST["uname"])) {
        $usernameErr = "Please enter your Username";
    } else {
        $uname = test_input($_POST["uname"]);
    }

    if (empty($_POST["upassword"])) {
        $passwordErr = "Please enter your Password";
    } else {
        $upassword = test_input($_POST["upassword"]);
    }

    if (empty($_POST["ic"])) {
        $icErr = "Please enter your IC number";
    } else {
        if (!preg_match("/^[0-9 -]*$/", $_POST["ic"])) {
            $icErr = "Please enter a valid IC number";
        } else {
            $ic = test_input($_POST["ic"]);
        }
    }

    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email address";
    } else {
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        } else {
            $email = test_input($_POST["email"]);
        }
    }

    if (empty($_POST["contact"])) {
        $contactErr = "Please enter your phone number";
    } else {
        if (!preg_match("/^[0-9 -]*$/", $_POST["contact"])) {
            $contactErr = "Please enter a valid phone number";
        } else {
            $contact = test_input($_POST["contact"]);
        }
    }

    if (empty($_POST["gender"])) {
        $genderErr = "* Gender is required!";
    } else {
        $gender = $_POST["gender"];
    }

    if (empty($_POST["address"])) {
        $addressErr = "Please enter your address";
    } else {
        $address = test_input($_POST["address"]);
    }

    // Nếu không có lỗi, thực hiện cập nhật thông tin
    if (empty($nameErr) && empty($emailErr) && empty($genderErr) && empty($addressErr) && empty($icErr) && empty($contactErr) && empty($usernameErr) && empty($passwordErr)) {
        try {
            // Cập nhật thông tin người dùng và thông tin khách hàng
            $stmt = $conn->prepare("UPDATE users SET UserName = :uname, Password = :upassword WHERE UserID = :userID");
            $stmt->bindParam(':uname', $uname);
            $stmt->bindParam(':upassword', $upassword);
            $stmt->bindParam(':userID', $_SESSION['id']);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE customer SET CustomerName = :name, CustomerPhone = :contact, 
                                    CustomerIC = :ic, CustomerEmail = :email, CustomerAddress = :address, 
                                    CustomerGender = :gender WHERE UserID = :userID");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':ic', $ic);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':userID', $_SESSION['id']);
            $stmt->execute();

            // Chuyển hướng về trang index sau khi cập nhật thành công
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            echo "Update failed: " . $e->getMessage();
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<html>
<link rel="stylesheet" href="style.css">
<body>
<header>
<blockquote>
	<a href="index.php"><img src="image/logo.png"></a>
</blockquote>
</header>
<blockquote>
<div class="container">
<form method="post"  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	<h1>Edit Profile:</h1>
	Full Name:<br><input type="text" name="name" placeholder="<?php echo $oName; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $nameErr;?></span><br><br>

	User Name:<br><input type="text" name="uname" placeholder="<?php echo $oUserName; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $usernameErr;?></span><br><br>

	New Password:<br><input type="password" name="upassword" placeholder="<?php echo $oPassword; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $passwordErr;?></span><br><br>

	IC Number:<br><input type="text" name="ic" placeholder="<?php echo $oIC; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $icErr;?></span><br><br>

	E-mail:<br><input type="text" name="email" placeholder="<?php echo $oEmail; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $emailErr;?></span><br><br>

	Mobile Number:<br><input type="text" name="contact" placeholder="<?php echo $oPhone; ?>">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $contactErr;?></span><br><br>

	<label>Gender:</label><br>
	<input type="radio" name="gender" <?php if (isset($gender) && $gender == "Male") echo "checked";?> value="Male">Male
	<input type="radio" name="gender" <?php if (isset($gender) && $gender == "Female") echo "checked";?> value="Female">Female
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $genderErr;?></span><br><br>

	<label>Address:</label><br>
    <textarea name="address" cols="50" rows="5" placeholder="<?php echo $oAddress; ?>"></textarea>
    <span class="error" style="color: red; font-size: 0.8em;"><?php echo $addressErr;?></span><br><br>
	
	<input class="button" type="submit" name="submitButton" value="Edit">
	<input class="button" type="button" name="cancel" value="Cancel" onClick="window.location='index.php';" />
</form>
</div>
</blockquote>
</center>
</body>
</html>