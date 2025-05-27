<?php
session_start();
session_destroy();
header("Location: /proyecto_videojuegos/index.php");

exit;
