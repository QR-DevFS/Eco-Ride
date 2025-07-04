<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // Configuration locale
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'gexxtxdmk4rhsy75');
} else {
    // Configuration distante (AWS RDS)
    define('DB_HOST', 'u3y93bv513l7zv6o.chr7pe7iynqr.eu-west-1.rds.amazonaws.com');
    define('DB_USER', 'dq5e7327qswvhthq');
    define('DB_PASS', 'yluqrzgxc4a9vns0');
    define('DB_NAME', 'gexxtxdmk4rhsy75');
}
?>