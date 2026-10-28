<?php
/**
 * config.php
 * Database connection for the Riverstone Consulting demo site.
 *
 * Credentials are read from environment variables so you never have to
 * hardcode your RDS endpoint/password into the PHP file itself.
 * Set these in your EC2 instance (see deploy_guide.md) e.g. via
 * /etc/environment, Apache's SetEnv, or a .env loaded by a wrapper.
 */

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';      // Your RDS endpoint goes here
$DB_NAME = getenv('DB_NAME') ?: 'riverstone';
$DB_USER = getenv('DB_USER') ?: 'admin';
$DB_PASS = getenv('DB_PASS') ?: 'changeme';
$DB_PORT = getenv('DB_PORT') ?: 3306;

$conn = mysqli_init();
$ok = mysqli_real_connect($conn, $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if (!$ok) {
    // Keep error detail server-side in real projects; shown here for lab convenience only.
    die("Database connection failed: " . mysqli_connect_error());
}
