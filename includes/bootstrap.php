<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    session_set_cookie_params([
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"]),
        "samesite" => "Lax",
    ]);
    session_start();
}
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/functions.php";
