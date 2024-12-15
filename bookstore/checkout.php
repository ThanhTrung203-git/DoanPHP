<html>
<body style="font-family:Arial; margin: 0 auto; background-color: #f2f2f2;">
<header>
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f2f2f2;
        }

        header {
            background-color: rgb(0,51,102);
            color: white;
            padding: 10px 0;
        }

        header img {
            height: 50px;
            margin-left: 20px;
        }

        .hi {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            margin-right: 20px;
        }

        .hi:hover {
            background-color: #45a049;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background-color: #45a049;
        }

        h2 {
            color: #000;
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        td img {
            width: 20%;
            border-radius: 4px;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .form-container {
            margin-top: 20px;
        }

        .form-container input[type="text"], .form-container input[type="email"], .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .form-container input[type="radio"] {
            margin-right: 10px;
        }

        .form-container label {
            font-size: 16px;
        }
    </style>
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

    try {
        // Kết nối PDO
        $conn = new PDO("mysql:host=$servername;dbname=bookstore", $username, $password);
        // Thiết lập chế độ lỗi
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $cID = 0;
    $sql = "SELECT CustomerID FROM customer WHERE UserID = :userID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $_SESSION['id']);
    $stmt->execute();
    $cID = $stmt->fetchColumn();

    if ($cID > 0) {
        // Cập nhật giỏ hàng
        $sql = "UPDATE cart SET CustomerID = :customerID WHERE CustomerID IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();

        // Chuyển dữ liệu từ giỏ hàng sang bảng đơn hàng
        $sql = "SELECT * FROM cart WHERE CustomerID = :customerID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cartItems as $row) {
            $sql = "INSERT INTO `order` (CustomerID, BookID, DatePurchase, Quantity, TotalPrice, Status) 
                    VALUES (:customerID, :bookID, CURRENT_TIME, :quantity, :totalPrice, 'N')";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bindParam(':customerID', $cID);
            $stmt2->bindParam(':bookID', $row['BookID']);
            $stmt2->bindParam(':quantity', $row['Quantity']);
            $stmt2->bindParam(':totalPrice', $row['TotalPrice']);
            $stmt2->execute();
        }

        // Xóa giỏ hàng
        $sql = "DELETE FROM cart WHERE CustomerID = :customerID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();

        // Lấy thông tin khách hàng
        $sql = "SELECT customer.CustomerName, customer.CustomerIC, customer.CustomerGender, customer.CustomerAddress, 
                customer.CustomerEmail, customer.CustomerPhone, `order`.`DatePurchase`
                FROM customer 
                JOIN `order` ON `order`.`CustomerID` = customer.CustomerID 
                WHERE `order`.`CustomerID` = :customerID LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        echo '<div class="container">';
        echo '<blockquote>';
        echo '<input class="button" style="float: right;" type="button" name="cancel" value="Continue Shopping" onClick="window.location=\'index.php\';" />';
        echo '<h2 style="color: #000;">Order Successful</h2>';
        echo "<table style='width:100%'>";
        echo "<tr><th>Order Summary</th><th></th></tr>";
        echo "<tr><td>Name: </td><td>" . htmlspecialchars($customer['CustomerName']) . "</td></tr>";
        echo "<tr><td>No.Number: </td><td>" . htmlspecialchars($customer['CustomerIC']) . "</td></tr>";
        echo "<tr><td>E-mail: </td><td>" . htmlspecialchars($customer['CustomerEmail']) . "</td></tr>";
        echo "<tr><td>Mobile Number: </td><td>" . htmlspecialchars($customer['CustomerPhone']) . "</td></tr>";
        echo "<tr><td>Gender: </td><td>" . htmlspecialchars($customer['CustomerGender']) . "</td></tr>";
        echo "<tr><td>Address: </td><td>" . htmlspecialchars($customer['CustomerAddress']) . "</td></tr>";
        echo "<tr><td>Date: </td><td>" . htmlspecialchars($customer['DatePurchase']) . "</td></tr>";
        echo "</table>";
        echo "</blockquote>";

        // Hiển thị chi tiết sản phẩm trong đơn hàng
        $sql = "SELECT book.BookTitle, book.Price, book.Image, `order`.`Quantity`, `order`.`TotalPrice`
                FROM `order`
                JOIN book ON `order`.`BookID` = book.BookID
                WHERE `order`.`CustomerID` = :customerID AND `order`.`Status` = 'N'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        echo "<table style='width:100%'>";
        echo "<tr><th>Product Details</th><th>Price</th><th>Quantity</th><th>Total Price</th></tr>";
        foreach ($result as $row) {
            echo "<tr><td>";
            echo '<img src="' . htmlspecialchars($row["Image"]) . '" width="20%" />';
            echo htmlspecialchars($row['BookTitle']);
            echo "</td><td>RM " . number_format($row['Price'], 2) . "</td><td>" . $row['Quantity'] . "</td><td>RM " . number_format($row['TotalPrice'], 2) . "</td></tr>";
            $total += $row['TotalPrice'];
        }
        echo "<tr><td colspan='3' style='text-align: right;'>Total Price:</td><td><b>RM " . number_format($total, 2) . "</b></td></tr>";
        echo "</table>";
        echo "</div>";

        // Cập nhật trạng thái đơn hàng
        $sql = "UPDATE `order` SET Status = 'y' WHERE CustomerID = :customerID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customerID', $cID);
        $stmt->execute();
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
                VALUES (:name, :contact, :ic, :email, :address, :gender)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':ic', $ic);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':gender', $gender);
        $stmt->execute();
        $id = $conn->lastInsertId();

        echo '<br /><br /><b>Order successfully placed. Thank you for shopping with us!</b>';
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
</body>
</html>
