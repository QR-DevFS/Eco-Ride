<!-- template.php -->

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? "Eco-Ride" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" type="text/css" href="accueil.css" />
</head>

<body>

  <?php require 'menu.php'; ?>

  <main class="container my-5">
    <?= $content ?>
  </main>

  <?php require 'footer.html'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Réinitialise les modales pour éviter leur affichage accidentel
  $(document).ready(function() {
    $('#registerModal, #connexionModal').removeClass('show').hide();
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
  });
  </script>

</body>

</html>