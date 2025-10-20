<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password: admin123\n";
echo "Generated Hash: " . $hash . "\n";
echo "Verification Test: " . (password_verify($password, '$2y$10$WxLwHYDaQk8fMr0zXlOPa.jmj9JH0AXgCww7PjQTJwVbYyYnjwLGi') ? "PASS" : "FAIL") . "\n";
?>