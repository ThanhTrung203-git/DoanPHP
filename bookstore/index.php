<html>
<meta http-equiv="Content-Type"'.' content="text/html; charset=utf8"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css">
<body>
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";

try {
    // Kết nối PDO
    $conn = new PDO("mysql:host=$servername;dbname=bookstore", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (isset($_POST['ac'])) {
        // Lấy dữ liệu từ form và thêm vào giỏ hàng
        $bookID = $_POST['ac'];
        $quantity = $_POST['quantity'];

        // Truy vấn thông tin sách từ bảng book
        $stmt = $conn->prepare("SELECT * FROM book WHERE BookID = :bookID");
        $stmt->bindParam(':bookID', $bookID);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $price = $row['Price'];
        $totalPrice = $price * $quantity;

        // Thêm vào giỏ hàng
        $stmt = $conn->prepare("INSERT INTO cart(BookID, Quantity, Price, TotalPrice) VALUES(:bookID, :quantity, :price, :totalPrice)");
        $stmt->bindParam(':bookID', $bookID);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':totalPrice', $totalPrice);
        $stmt->execute();
    }

    if (isset($_POST['delc'])) {
        // Xóa tất cả các sản phẩm trong giỏ hàng
        $stmt = $conn->prepare("DELETE FROM cart");
        $stmt->execute();
    }

    // Lấy tất cả sách từ bảng book
    $stmt = $conn->prepare("SELECT * FROM book");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin giỏ hàng
    $stmt = $conn->prepare("SELECT book.BookTitle, book.Image, cart.Price, cart.Quantity, cart.TotalPrice FROM book, cart WHERE book.BookID = cart.BookID");
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<?php
if (isset($_SESSION['id'])) {
    echo '<header>';
    echo '<blockquote>';
    echo '<a href="index.php"><img src="image/logo.png"></a>';
    echo '<form class="hf" action="logout.php"><input class="hi" type="submit" name="submitButton" value="Logout"></form>';
    echo '<form class="hf" action="edituser.php"><input class="hi" type="submit" name="submitButton" value="Edit Profile"></form>';
    echo '</blockquote>';
    echo '</header>';
}

if (!isset($_SESSION['id'])) {
    echo '<header>';
    echo '<blockquote>';
    echo '<a href="index.php"><img src="image/logo.png"></a>';
    echo '<form class="hf" action="Register.php"><input class="hi" type="submit" name="submitButton" value="Register"></form>';
    echo '<form class="hf" action="login.php"><input class="hi" type="submit" name="submitButton" value="Login"></form>';
    echo '</blockquote>';
    echo '</header>';
}

echo '<blockquote>';
echo "<table id='myTable' style='width:80%; float:left'>";
echo "<tr>";
foreach ($books as $row) {
    echo "<td>";
    echo "<table>";
    echo '<tr><td>' . '<img src="' . $row["Image"] . '" width="80%"></td></tr><tr><td style="padding: 5px;">Title: ' . $row["BookTitle"] . '</td></tr><tr><td style="padding: 5px;">ISBN: ' . $row["ISBN"] . '</td></tr><tr><td style="padding: 5px;">Author: ' . $row["Author"] . '</td></tr><tr><td style="padding: 5px;">Type: ' . $row["Type"] . '</td></tr><tr><td style="padding: 5px;">RM' . $row["Price"] . '</td></tr><tr><td style="padding: 5px;">
    <form action="" method="post">
    Quantity: <input type="number" value="1" name="quantity" style="width: 20%"/><br>
    <input type="hidden" value="' . $row['BookID'] . '" name="ac"/>
    <input class="button" type="submit" value="Add to Cart"/>
    </form></td></tr>';
    echo "</table>";
    echo "</td>";
}
echo "</tr>";
echo "</table>";

echo "<table style='width:20%; float:right;'>";
echo "<th style='text-align:left;'><i class='fa fa-shopping-cart' style='font-size:24px'></i> Cart <form style='float:right;' action='' method='post'><input type='hidden' name='delc'/><input class='cbtn' type='submit' value='Empty Cart'></form></th>";
$total = 0;
foreach ($cartItems as $row) {
    echo "<tr><td>";
    echo '<img src="' . $row["Image"] . '" width="20%"><br>';
    echo $row['BookTitle'] . "<br>RM" . $row['Price'] . "<br>";
    echo "Quantity: " . $row['Quantity'] . "<br>";
    echo "Total Price: RM" . $row['TotalPrice'] . "</td></tr>";
    $total += $row['TotalPrice'];
}
echo "<tr><td style='text-align: right;background-color: #f2f2f2;'>";
echo "Total: <b>RM" . $total . "</b><center><form action='checkout.php' method='post'><input class='button' type='submit' name='checkout' value='CHECKOUT'></form></center>";
echo "</td></tr>";
echo "</table>";
echo '</blockquote>';
?>

</body>
</html>