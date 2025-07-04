<?php
$title = "Affichage";
ob_start();
require "recherche.php";
$content = ob_get_clean();
require "template.php";
?>