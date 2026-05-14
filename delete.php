<?php
include 'db.php';

$id=$_GET['id'];

mysqli_query($conn,"DELETE FROM items WHERE id=$id");

header("Location:index.php");
?>