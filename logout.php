<?php
require_once __DIR__ . '/functions.php';

session_unset();
session_destroy();
redirect('login.php');
