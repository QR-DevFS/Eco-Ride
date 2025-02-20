<?php

require "contact_trame.html";

if (isset($_GET['success'])) {
    echo '<h3> Votre message a été envoyé avec succès !</h3>';
} elseif (isset($_GET['error'])) {
    echo '<h3> Une erreur est survenue. Veuillez réessayer.<h3>';
}
?>