<?php
session_start();
session_unset(); // optional but clears session data
session_destroy();
header("Location: login.php");
exit();
