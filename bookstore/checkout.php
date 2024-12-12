<html>
<body style="font-family:Arial; margin: 0 auto; background-color: #f2f2f2;">
<header>
<blockquote>
	<img src="image/logo.png">
	<input class="hi" style="float: right; margin: 2%;" type="button" name="cancel" value="Home" onClick="window.location='index.php';" />
</blockquote>
</header>
<?php
session_start();

if (isset($_SESSION['id'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, "bookstore"); 

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $cID = 0;
    $sql = "SELECT CustomerID FROM customer WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($cID);
    $stmt->fetch();
    $stmt->close();

    if ($cID > 0) {
        // Cập nhật giỏ hàng
        $sql = "UPDATE cart SET CustomerID = ? WHERE CustomerID IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cID);
        $stmt->execute();
        $stmt->close();

        // Chuyển dữ liệu từ giỏ hàng sang bảng đơn hàng
        $sql = "SELECT * FROM cart WHERE CustomerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sql = "INSERT INTO `order` (CustomerID, BookID, DatePurchase, Quantity, TotalPrice, Status) 
                    VALUES (?, ?, CURRENT_TIME, ?, ?, 'N')";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("iiid", $cID, $row['BookID'], $row['Quantity'], $row['TotalPrice']);
            $stmt2->execute();
            $stmt2->close();
        }
        $stmt->close();

        // Xóa giỏ hàng
        $sql = "DELETE FROM cart WHERE CustomerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cID);
        $stmt->execute();
        $stmt->close();

        // Hiển thị thông tin đơn hàng
        $sql = "SELECT customer.CustomerName, customer.CustomerIC, customer.CustomerGender, customer.CustomerAddress, 
                       customer.CustomerEmail, customer.CustomerPhone, book.BookTitle, book.Price, book.Image, 
                       `order`.`DatePurchase`, `order`.`Quantity`, `order`.`TotalPrice`
                FROM customer 
                JOIN `order` ON `order`.`CustomerID` = customer.CustomerID 
                JOIN book ON `order`.`BookID` = book.BookID 
                WHERE `order`.`Status` = 'N' AND `order`.`CustomerID` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cID);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="container">';
        echo '<blockquote>';
        echo '<input class="button" style="float: right;" type="button" name="cancel" value="Continue Shopping" onClick="window.location=\'index.php\';" />';
        echo '<h2 style="color: #000;">Order Successful</h2>';
        echo "<table style='width:100%'>";
        echo "<tr><th>Order Summary</th><th></th></tr>";
        $row = $result->fetch_assoc();
        echo "<tr><td>Name: </td><td>" . htmlspecialchars($row['CustomerName']) . "</td></tr>";
        echo "<tr><td>No.Number: </td><td>" . htmlspecialchars($row['CustomerIC']) . "</td></tr>";
        echo "<tr><td>E-mail: </td><td>" . htmlspecialchars($row['CustomerEmail']) . "</td></tr>";
        echo "<tr><td>Mobile Number: </td><td>" . htmlspecialchars($row['CustomerPhone']) . "</td></tr>";
        echo "<tr><td>Gender: </td><td>" . htmlspecialchars($row['CustomerGender']) . "</td></tr>";
        echo "<tr><td>Address: </td><td>" . htmlspecialchars($row['CustomerAddress']) . "</td></tr>";
        echo "<tr><td>Date: </td><td>" . htmlspecialchars($row['DatePurchase']) . "</td></tr>";
        echo "</blockquote>";

        // Hiển thị chi tiết sản phẩm trong đơn hàng
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td style='border-top: 2px solid #ccc;'>";
            echo '<img src="' . htmlspecialchars($row["Image"]) . '" width="20%"></td><td style="border-top: 2px solid #ccc;">';
            echo htmlspecialchars($row['BookTitle']) . "<br>RM" . number_format($row['Price'], 2) . "<br>";
            echo "Quantity: " . $row['Quantity'] . "<br>";
            echo "</td></tr>";
            $total += $row['TotalPrice'];
        }
        echo "<tr><td style='background-color: #ccc;'></td><td style='text-align: right;background-color: #ccc;'>Total Price: <b>RM" . number_format($total, 2) . "</b></td></tr>";
        echo "</table>";
        echo "</div>";

        // Cập nhật trạng thái đơn hàng
        $sql = "UPDATE `order` SET Status = 'y' WHERE CustomerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cID);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_POST['submitButton'])) {
    // Xử lý dữ liệu người dùng
    $name = $email = $gender = $address = $ic = $contact = "";
    $nameErr = $emailErr = $genderErr = $addressErr = $icErr = $contactErr = "";

    // Kiểm tra dữ liệu đầu vào
    if (empty($_POST["name"])) {
        $nameErr = "Please enter your name";
    } else {
        $name = test_input($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed";
        }
    }

    if (empty($_POST["ic"])) {
        $icErr = "Please enter your IC number";
    } else {
        $ic = test_input($_POST["ic"]);
        if (!preg_match("/^[0-9 -]*$/", $ic)) {
            $icErr = "Please enter a valid IC number";
        }
    }

    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email address";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    if (empty($_POST["contact"])) {
        $contactErr = "Please enter your phone number";
    } else {
        $contact = test_input($_POST["contact"]);
        if (!preg_match("/^[0-9 -]*$/", $contact)) {
            $contactErr = "Please enter a valid phone number";
        }
    }

    if (empty($_POST["gender"])) {
        $genderErr = "* Gender is required!";
    } else {
        $gender = test_input($_POST["gender"]);
    }

    if (empty($_POST["address"])) {
        $addressErr = "Please enter your address";
    } else {
        $address = test_input($_POST["address"]);
    }

    // Nếu không có lỗi, tiếp tục xử lý đơn hàng
    if (empty($nameErr) && empty($icErr) && empty($emailErr) && empty($contactErr) && empty($genderErr) && empty($addressErr)) {
        $sql = "INSERT INTO customer (CustomerName, CustomerPhone, CustomerIC, CustomerEmail, CustomerAddress, CustomerGender) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $contact, $ic, $email, $address, $gender);
        $stmt->execute();
        $stmt->close();

        // Lấy CustomerID
        $sql = "SELECT CustomerID FROM customer WHERE CustomerName = ? AND CustomerIC = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $ic);
        $stmt->execute();
        $stmt->bind_result($cID);
        $stmt->fetch();
        $stmt->close();

        if ($cID > 0) {
            // Cập nhật giỏ hàng và xử lý đơn hàng
            $sql = "UPDATE cart SET CustomerID = ? WHERE CustomerID IS NULL";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $cID);
            $stmt->execute();
            $stmt->close();
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

<style> 
header {
	background-color: rgb(0,51,102);
	width: 100%;
}
header img {
	margin: 1%;
}
header .hi{
    background-color: #fff;
    border: none;
    border-radius: 20px;
    text-align: center;
    transition-duration: 0.5s; 
    padding: 8px 30px;
    cursor: pointer;
    color: #000;
    margin-top: 15%;
}
header .hi:hover{
    background-color: #ccc;
}
form{
	margin-top: 1%;
	float: left;
	width: 40%;
	color: #000;
}
input[type=text] {
	padding: 5px;
    border-radius: 3px;
    box-sizing: border-box;
    border: 2px solid #ccc;
    transition: 0.5s;
    outline: none;
}
input[type=text]:focus {
    border: 2px solid rgb(0,51,102);
}
textarea {
	outline: none;
	border: 2px solid #ccc;
}
textarea:focus {
	border: 2px solid rgb(0,51,102);
}
.button{
    background-color: rgb(0,51,102);
    border: none;
    border-radius: 20px;
    text-align: center;
    transition-duration: 0.5s; 
    padding: 8px 30px;
    cursor: pointer;
    color: #fff;
}
.button:hover {
    background-color: rgb(102,255,255);
    color: #000;
}
table {
    border-collapse: collapse;
    width: 60%;
    float: right;
}
th, td {
    text-align: left;
    padding: 8px;
}
tr{background-color: #fff;}

th {
    background-color: rgb(0,51,102);
    color: white;
}
.container {
	width: 50%;
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 20px;
    margin: 0 auto;
}
</style>
<blockquote>
<?php
if(!isset($_SESSION['id'])){
	echo "<form method='post'  action=''>";

	echo 'Name:<br><input type="text" name="name" placeholder="Full Name">';
	echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $nameErr;?></span><br><br>';

	echo 'IC Number:<br><input type="text" name="ic" placeholder="xxxxxx-xx-xxxx">';
	echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $icErr;?></span><br><br>';

	echo 'E-mail:<br><input type="text" name="email" placeholder="example@email.com">';
	echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $emailErr;?></span><br><br>';

	echo 'Mobile Number:<br><input type="text" name="contact" placeholder="012-3456789">';
	echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $contactErr;?></span><br><br>';

	echo '<label>Gender:</label><br>';
	echo '<input type="radio" name="gender" if (isset($gender) && $gender == "Male") echo "checked"; value="Male">Male';
	echo '<input type="radio" name="gender" if (isset($gender) && $gender == "Female") echo "checked"; value="Female">Female';
	echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $genderErr;?></span><br><br>';

	echo '<label>Address:</label><br>';
	   echo '<textarea name="address" cols="30" rows="5" placeholder="Address"></textarea>';
	   echo '<span class="error" style="color: red; font-size: 0.8em;"><?php echo $addressErr;?></span><br><br>';
?>
<input class="button" type="button" name="cancel" value="Cancel" onClick="window.location='index.php';" />
<?php
	echo '<input class="button" type="submit" name="submitButton" value="CHECKOUT">';
	echo '</form><br><br>';
}

if(isset($_POST['submitButton'])){
	$servername = "localhost";
	$username = "root";
	$password = "";

	$conn = new mysqli($servername, $username, $password); 

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	$sql = "USE bookstore";
	$conn->query($sql);

	$sql = "SELECT customer.CustomerName, customer.CustomerIC, customer.CustomerGender, customer.CustomerAddress, customer.CustomerEmail, customer.CustomerPhone, book.BookTitle, book.Price, book.Image, `order`.`DatePurchase`, `order`.`Quantity`, `order`.`TotalPrice`
		FROM customer, book, `order`
		WHERE `order`.`CustomerID` = customer.CustomerID AND `order`.`BookID` = book.BookID AND `order`.`Status` = 'N' AND `order`.`CustomerID` = ".$cID."";
	$result = $conn->query($sql);

	echo '<table style="width: 40%">';
	echo "<tr><th>Order Summary</th>";
	echo "<th></th></tr>";
	$row = $result->fetch_assoc();
	echo "<tr><td>Name: </td><td>".$row['CustomerName']."</td></tr>";
	echo "<tr><td>No.Number: </td><td>".$row['CustomerIC']."</td></tr>";
	echo "<tr><td>E-mail: </td><td>".$row['CustomerEmail']."</td></tr>";
	echo "<tr><td>Mobile Number: </td><td>".$row['CustomerPhone']."</td></tr>";
	echo "<tr><td>Gender: </td><td>".$row['CustomerGender']."</td></tr>";
	echo "<tr><td>Address: </td><td>".$row['CustomerAddress']."</td></tr>";
	echo "<tr><td>Date: </td><td>".$row['DatePurchase']."</td></tr>";

	$sql = "SELECT customer.CustomerName, customer.CustomerIC, customer.CustomerGender, customer.CustomerAddress, customer.CustomerEmail, customer.CustomerPhone, book.BookTitle, book.Price, book.Image, `order`.`DatePurchase`, `order`.`Quantity`, `order`.`TotalPrice`
		FROM customer, book, `order`
		WHERE `order`.`CustomerID` = customer.CustomerID AND `order`.`BookID` = book.BookID AND `order`.`Status` = 'N' AND `order`.`CustomerID` = ".$cID."";
	$result = $conn->query($sql);
	$total = 0;
	while($row = $result->fetch_assoc()){
		echo "<tr><td style='border-top: 2px solid #ccc;'>";
		echo '<img src="'.$row["Image"].'"width="20%"></td><td style="border-top: 2px solid #ccc;">';
    	echo $row['BookTitle']."<br>RM".$row['Price']."<br>";
    	echo "Quantity: ".$row['Quantity']."<br>";
    	echo "</td></tr>";
    	$total += $row['TotalPrice'];
	}
	echo "<tr><td style='background-color: #ccc;'></td><td style='text-align: right;background-color: #ccc;'>Total Price: <b>RM".$total."</b></td></tr>";
	echo "</table>";

	$sql = "UPDATE `order` SET Status = 'y' WHERE CustomerID = ".$cID."";
	$conn->query($sql);
}
?>
</blockquote>
</body>
</html>