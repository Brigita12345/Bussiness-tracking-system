<?php
    include("connection.php");
    session_abort();
    session_unset();
    session_destroy();
    header("location:login.php");
    exit();
?>