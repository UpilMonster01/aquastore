<?php
require "../config/db.php";

unset($_SESSION['user']);

header("Location: ../index.php");
exit;