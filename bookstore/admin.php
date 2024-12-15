<?php
session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin, chuyển hướng về trang login
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "Session admin is not set correctly."; // Thêm dòng này để kiểm tra
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bookstore";

// Tạo kết nối
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Thêm sách
if (isset($_POST['addBook'])) {
    $bookID = $_POST['bookID'];
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $author = $_POST['author'];
    $type = $_POST['type'];
    $image = $_POST['image'];

    $sql = "INSERT INTO Book (BookID, BookTitle, ISBN, Price, Author, Type, Image) 
            VALUES (:bookID, :title, :isbn, :price, :author, :type, :image)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bookID', $bookID);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':image', $image);
    
    // Thực thi câu lệnh thêm sách
    if ($stmt->execute()) {
        echo '<p>Book added successfully!</p>';
    } else {
        echo '<p style="color: red;">Failed to add book.</p>';
    }
}

// Sửa sách
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
    
    // Thực thi câu lệnh sửa sách
    if ($stmt->execute()) {
        echo '<p>Book updated successfully!</p>';
    } else {
        echo '<p style="color: red;">Failed to update book.</p>';
    }
}

// Xóa sách
if (isset($_GET['deleteBook'])) {
    $bookID = $_GET['deleteBook'];

    $sql = "DELETE FROM Book WHERE BookID = :bookID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bookID', $bookID);
    
    // Thực thi câu lệnh xóa sách
    if ($stmt->execute()) {
        echo '<p>Book deleted successfully!</p>';
    } else {
        echo '<p style="color: red;">Failed to delete book.</p>';
    }
}

// Lấy danh sách sách
$sql = "SELECT * FROM Book";
$stmt = $conn->query($sql);
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        /* Style cho trang quản trị */
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
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
        }
        .button-danger {
            background-color: #f44336;
        }
        .button:hover {
            opacity: 0.8;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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
    <h1>Admin Panel</h1>
</header>

<div class="container">

    <!-- Form thêm sách -->
    <div class="form-container">
        <h2>Add New Book</h2>
        <form method="POST" action="admin.php">
            <input type="text" name="bookID" placeholder="Book ID" required><br>
            <input type="text" name="title" placeholder="Book Title" required><br>
            <input type="text" name="isbn" placeholder="ISBN" required><br>
            <input type="number" name="price" placeholder="Price" required><br>
            <input type="text" name="author" placeholder="Author" required><br>
            <input type="text" name="type" placeholder="Type" required><br>
            <input type="text" name="image" placeholder="Image URL" required><br>
            <input type="submit" name="addBook" value="Add Book" class="button">
        </form>
    </div>

    <!-- Hiển thị danh sách sách -->
    <h2>Book List</h2>
    <table>
        <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>ISBN</th>
            <th>Price</th>
            <th>Author</th>
            <th>Type</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($books as $book): ?>
            <tr>
                <td><?php echo htmlspecialchars($book['BookID']); ?></td>
                <td><?php echo htmlspecialchars($book['BookTitle']); ?></td>
                <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                <td>RM <?php echo number_format($book['Price'], 2); ?></td>
                <td><?php echo htmlspecialchars($book['Author']); ?></td>
                <td><?php echo htmlspecialchars($book['Type']); ?></td>
                <td><img src="<?php echo htmlspecialchars($book['Image']); ?>" width="50"></td>
                <td>
                    <a href="edit.php?bookID=<?php echo $book['BookID']; ?>" class="button">Edit</a>
                    <a href="admin.php?deleteBook=<?php echo $book['BookID']; ?>" class="button button-danger" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
