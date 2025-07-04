<?php
session_start();
?>
<?php
$title = "Covoiturages";
ob_start();

require "covoit.php";
$content = ob_get_clean();
require "template.php";

?>