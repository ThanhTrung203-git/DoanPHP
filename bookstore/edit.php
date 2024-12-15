<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookstore";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['bookID'])) {
    $bookID = $_GET['bookID'];

    $sql = "SELECT * FROM Book WHERE BookID = :bookID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bookID', $bookID);
    $stmt->execute();
    $book = $stmt->fetch();
}

if (isset($_POST['editBook'])) {
    $bookID = $_POST['bookID'];
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $author = $_POST['author'];
    $type = $_POST['type'];
    $image = $_POST['image'];

    $sql = "UPDATE Book SET BookTitle = :title, ISBN = :isbn, Price = :price, Author = :author, Type = :type, Image = :image 
            WHERE BookID = :bookID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bookID', $bookID);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':image', $image);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<header>
    <h1>Edit Book</h1>
</header>

<div class="container">

    <div class="form-container">
        <h2>Edit Book Details</h2>
        <form method="POST" action="edit.php?bookID=<?php echo $book['BookID']; ?>">
            <input type="text" name="bookID" value="<?php echo htmlspecialchars($book['BookID']); ?>" readonly><br>
            <input type="text" name="title" value="<?php echo htmlspecialchars($book['BookTitle']); ?>" required><br>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['ISBN']); ?>" required><br>
            <input type="number" name="price" value="<?php echo htmlspecialchars($book['Price']); ?>" required><br>
            <input type="text" name="author" value="<?php echo htmlspecialchars($book['Author']); ?>" required><br>
            <input type="text" name="type" value="<?php echo htmlspecialchars($book['Type']); ?>" required><br>
            <input type="text" name="image" value="<?php echo htmlspecialchars($book['Image']); ?>" required><br>
            <input type="submit" name="editBook" value="Save Changes" class="button">
        </form>
    </div>

</div>

</body>
</html>
