<?php
// config.php
define('ADMIN_EMAIL', 'admin@webcord.com');
define('ADMIN_PASSWORD', 'root8962');     // change this immediately after first use
define('SECRET_KEY', 'change-this-to-strong-random-string-2025');  // used for simple JWT-like token
define('CREDIT_PER_MINUTE', 2);
define('TOOL_COST', 90);
define('DATA_FILE', __DIR__ . '/users.json');
?>
