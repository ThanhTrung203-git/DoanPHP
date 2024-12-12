<?php
session_start();
$nameErr = $emailErr = $genderErr = $addressErr = $icErr = $contactErr = $usernameErr = $passwordErr = "";
$name = $email = $gender = $address = $ic = $contact = $uname = $upassword = "";
$cID;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra tên
    if (empty($_POST["name"])) {
        $nameErr = "Please enter your name";
    } else {
        if (!preg_match("/^[a-zA-Z ]*$/", $_POST["name"])) {
            $nameErr = "Only letters and white space allowed";
        } else {
            $name = test_input($_POST["name"]);
        }
    }

    // Kiểm tra tên người dùng
    if (empty($_POST["uname"])) {
        $usernameErr = "Please enter your Username";
    } else {
        $uname = test_input($_POST["uname"]);
    }

    // Kiểm tra mật khẩu
    if (empty($_POST["upassword"])) {
        $passwordErr = "Please enter your Password";
    } else {
        $upassword = test_input($_POST["upassword"]);
    }

    // Kiểm tra số IC
    if (empty($_POST["ic"])) {
        $icErr = "Please enter your IC number";
    } else {
        if (!preg_match("/^[0-9 -]*$/", $_POST["ic"])) {
            $icErr = "Please enter a valid IC number";
        } else {
            $ic = test_input($_POST["ic"]);
        }
    }

    // Kiểm tra email
    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email address";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Kiểm tra số điện thoại
    if (empty($_POST["contact"])) {
        $contactErr = "Please enter your phone number";
    } else {
        if (!preg_match("/^[0-9 -]*$/", $_POST["contact"])) {
            $contactErr = "Please enter a valid phone number";
        } else {
            $contact = test_input($_POST["contact"]);
        }
    }

    // Kiểm tra giới tính
    if (empty($_POST["gender"])) {
        $genderErr = "* Gender is required!";
    } else {
        $gender = test_input($_POST["gender"]);
    }

    // Kiểm tra địa chỉ
    if (empty($_POST["address"])) {
        $addressErr = "Please enter your address";
    } else {
        $address = test_input($_POST["address"]);
    }

    // Nếu không có lỗi, thực hiện thao tác lưu dữ liệu vào cơ sở dữ liệu
    if (empty($nameErr) && empty($emailErr) && empty($genderErr) && empty($addressErr) && empty($icErr) && empty($contactErr) && empty($usernameErr) && empty($passwordErr)) {

        try {
            // Kết nối PDO
            $pdo = new PDO("mysql:host=localhost;dbname=bookstore", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($upassword, PASSWORD_DEFAULT);

            // Chèn dữ liệu vào bảng users
            $sql = "INSERT INTO users(UserName, Password) VALUES(:uname, :upassword)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':uname', $uname);
            $stmt->bindParam(':upassword', $hashedPassword);
            $stmt->execute();

            // Lấy UserID của người dùng vừa đăng ký
            $userID = $pdo->lastInsertId();

            // Chèn dữ liệu vào bảng customer
            $sql = "INSERT INTO customer(CustomerName, CustomerPhone, CustomerIC, CustomerEmail, CustomerAddress, CustomerGender, UserID) 
                    VALUES(:name, :contact, :ic, :email, :address, :gender, :userID)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':ic', $ic);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();

            // Chuyển hướng về trang chủ
            header("Location:index.php");

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        // Đóng kết nối PDO
        $pdo = null;
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
	<h1>Register:</h1>
	Full Name:<br><input type="text" name="name" placeholder="Full Name">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $nameErr;?></span><br><br>

	User Name:<br><input type="text" name="uname" placeholder="User Name">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $usernameErr;?></span><br><br>

	New Password:<br><input type="password" name="upassword" placeholder="Password">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $passwordErr;?></span><br><br>

	IC Number:<br><input type="text" name="ic" placeholder="xxxxxx-xx-xxxx">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $icErr;?></span><br><br>

	E-mail:<br><input type="text" name="email" placeholder="example@email.com">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $emailErr;?></span><br><br>

	Mobile Number:<br><input type="text" name="contact" placeholder="012-3456789">
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $contactErr;?></span><br><br>

	<label>Gender:</label><br>
	<input type="radio" name="gender" <?php if (isset($gender) && $gender == "Male") echo "checked";?> value="Male">Male
	<input type="radio" name="gender" <?php if (isset($gender) && $gender == "Female") echo "checked";?> value="Female">Female
	<span class="error" style="color: red; font-size: 0.8em;"><?php echo $genderErr;?></span><br><br>

	<label>Address:</label><br>
    <textarea name="address" cols="50" rows="5" placeholder="Address"></textarea>
    <span class="error" style="color: red; font-size: 0.8em;"><?php echo $addressErr;?></span><br><br>

	<input class="button" type="submit" name="submitButton" value="Submit">
	<input class="button" type="button" name="cancel" value="Cancel" onClick="window.location='index.php';" />
</form>
</div>
</blockquote>
</center>
</body>
</html>