<?php require_once('mysql.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
        $sql = "SELECT * FROM mdl_event";
        $data = $conn->query($sql);
        $data = $data->fetch_all(MYSQLI_ASSOC);
        print_r($data);
    ?>
    <table>
        <thead>

        </thead>
        <tbody>

        </tbody>
    </table>
</body>
</html>