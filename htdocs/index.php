<?php
try {
    require_once __DIR__ . "/backend/controller.php";
    new Controller();
} catch (Exception $e) {
    header('Content-Type: text/plain');
    var_dump($e);
}