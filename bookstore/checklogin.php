<?php
session_start();
if(isset($_POST['username']) && isset($_POST['pwd'])){
    $username = $_POST['username'];
    $pwd = $_POST['pwd'];

    include "connectDB.php";
     
    $sql = "SELECT * FROM Users WHERE UserName = :username AND Password = :pwd;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':username' => $username,
        ':pwd' => $pwd       
    ));
    
    if($stmt->rowCount() > 0){
        // Lấy thông tin người dùng
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lưu thông tin vào session
        $_SESSION['id'] = $row['UserID'];
        $_SESSION['role'] = $row['Role'];  // Giả sử cột 'Role' lưu thông tin vai trò, với giá trị 'admin' hoặc 'customer'
        
        // Kiểm tra vai trò và set session cho admin
        if ($row['Role'] == 'admin') {
            $_SESSION['admin'] = true;  // Nếu là admin, set session cho admin
        } else {
            $_SESSION['admin'] = false; // Nếu là khách hàng, set session cho khách
        }
        
        // Chuyển hướng đến trang admin nếu là admin
        if ($row['Role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");  // Nếu là khách hàng, chuyển hướng tới trang chính
        }
    } else {
        echo '<span style="color: red;">Login Fail</span>';
        header("Location: login.php?errcode=1");
    }
}
?>
