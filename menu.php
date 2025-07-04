<!--<!DOCTYPE html>
<html lang="fr">
!-->
<?php
if (session_status() == PHP_SESSION_NONE) {
session_start();
}
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />

  <link rel="stylesheet" href="modal.css" />
</head>

<body>
  <header>
    <nav class="navbar">
      <div class="container-fluid">
        <div class="poslog">
          <a class="navbar-brand" href="Index.php">
            <img src="img/logo.png" class="poslogo" alt="Logo Ecordie" />
          </a>
        </div>
        <div class="container-fluid z-1">
          <div class="mt-5">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link active" href="Index.php" aria-current="page">Accueil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Index_pagecovoit.php">Covoiturages</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Index_pagecontact.php">Contact</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  Mon compte
                </a>
                <ul class="dropdown-menu text-center w-25">
                  <?php if (isset($_SESSION['utilisateur_id']) && isset($_SESSION['pseudo'])): ?>
                  <li><span class="dropdown-item disabled">Connecté :
                      <?= htmlspecialchars($_SESSION['pseudo']) ?></span></li>
                  <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employe'): ?>
                  <li><a class="dropdown-item" href="espace_employe_admin.php">Mon espace</a></li>
                  <?php else: ?>
                  <li><a class="dropdown-item" href="espace_util.php">Mon espace</a></li>
                  <?php endif; ?>
                  <li><a class="dropdown-item" href="deconnexion.php">Se déconnecter</a></li>
                  <?php else: ?>
                  <li><button class="dropdown-item" id="openRegisterModal">S'inscrire</button></li>
                  <li><button class="dropdown-item" id="openConnexionModal">Se connecter</button></li>
                  <?php endif; ?>
                </ul>
              </li>

              <ul class="dropdown-menu text-center w-25">
                <li>
                  <button class="dropdown-item" id="openRegisterModal" href="#">
                    S'inscrire
                  </button>
                </li>
                <li>
                  <button class="dropdown-item" id="openConnexionModal" href="#">
                    Se connecter
                  </button>
                </li>
              </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>
  <main>
    <div id="registerModal" class="modal fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Créer un compte</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="registerForm">
              <div class="form-group">
                <label for="pseudo">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="pseudo" name="pseudo" required />
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required />
              </div>
              <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required />
              </div>
              <button type="button" class="btn btn-success mt-4" onclick="submitRegister()">
                S'inscrire
              </button>
              <p>Don de 20 crédits à l'inscription</p>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div id="connexionModal" class="modal fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Créer un compte</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="ConnexionForm">
              <div class="form-group">
                <label for="pseudoConnexion">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="pseudoConnexion" name="pseudoConnexion" required />
              </div>

              <div class="form-group">
                <label for="passwordConnexion">Mot de passe</label>
                <input type="password" class="form-control" id="passwordConnexion" name="passwordConnexion" required />
              </div>
              <button type="button" class="btn btn-success mt-4" onclick="submitConnexion()">
                Se connecter
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
  $(document).ready(function() {
    $("#openRegisterModal").click(function(event) {
      event.preventDefault();

      $("#registerModal").modal("show");
    });
  });

  function submitRegister() {
    const pseudo = document.getElementById("pseudo").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    console.log("Données envoyées:", {
      pseudo,
      email,
      password
    });

    fetch("registerinscription.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          pseudo,
          email,
          password
        }),
      })
      .then(response => {
        console.log("Réponse brute:", response);
        return response.text();
      })
      .then(text => {
        console.log("Réponse texte:", text);
        try {
          const data = JSON.parse(text);
          if (data.success) {
            alert("Votre compte a bien été créé");
            document.activeElement.blur();
            $("#registerModal").modal("hide");
            document.getElementById("registerForm").reset();
            window.open("#");
          } else {
            alert(data.message || "Une erreur est survenue lors de l'inscription.");
          }
        } catch (e) {
          console.error("Erreur de parsing JSON:", e);
          console.log("Contenu de la réponse:", text);
          alert("Le serveur a renvoyé une réponse invalide. Voir la console pour plus de détails.");
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("Une erreur est survenue lors de l'inscription.");
      });
  }
  </script>
  <script>
  $(document).ready(function() {
    $("#openConnexionModal").click(function(event) {
      event.preventDefault();

      $("#connexionModal").modal("show");
    });
  });

  function submitConnexion() {
    const pseudoConnexion = document.getElementById("pseudoConnexion").value;
    const passwordConnexion = document.getElementById("passwordConnexion").value;

    fetch("registerConnexion.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          pseudoConnexion,
          passwordConnexion
        }),
      })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          $("#connexionModal").modal("hide");
          document.getElementById("ConnexionForm").reset();
          // Recharge la page pour prendre en compte la session et afficher le menu adapté
          location.reload();
        } else {
          alert(data.message || "Erreur de connexion.");
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Erreur lors de la connexion.");
      });
  }
  </script>
</body>

</html>