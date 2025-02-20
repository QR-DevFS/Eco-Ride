<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ecoride</title>
  <link rel="stylesheet" type="text/css" href="accueil.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
</head>

<body>

  <article>
    <section>
      <div class="container-fluid">
        <div class="posfiltre">
          <h3>Filtre</h3>
          <form method="post" action="">
            <div class="container-fluid padding">
              <h5 class="pb-3">Type de trajet</h5>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="trajetecologique" name="energie[]" value="Ecolo"
                  data-category="trajet" />
                <label class="form-check-label" for="trajetecologique">Ecologique ( Voiture électrique )</label>
                <br />
                <input class="form-check-input" type="checkbox" id="trajetnormal" name="energie[]" value="classique" />
                <label class="form-check-label" for="trajetnormal">
                  Classique( Voiture non-électrique )</label>
              </div>
            </div>
            <div class="container-fluid padding">
              <h5 class="pb-3">Prix du trajet</h5>
              <div class="form-floating widthinput">
                <input type="number" class="form-control" id="prixmax" name="prixmax" placeholder="Prix maximum" />
                <label for="prixmax">Prix maximum</label>
              </div>
            </div>
            <div class="container-fluid padding">
              <h5 class="pb-3">Durée du trajet</h5>
              <div class="form-floating widthinput">
                <input type="number" class="form-control" id="dureemax" name="dureemax" placeholder="Prix maximum" />
                <label for="dureemax">Durée maximum</label>
              </div>
            </div>

            <div class="container-fluid padding">
              <h5 class="pb-3">Note covoitureur</h5>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="5etoiles" name="note[]" ; value="5" />
                <label class="form-check-label" for="5etoiles">5 étoiles
                </label>
                <br />
                <input class="form-check-input" type="checkbox" id="4etoiles" name="note[]" ; value="4" />
                <label class="form-check-label" for="4etoiles">4 étoiles
                </label>
                <br />
                <input class="form-check-input" type="checkbox" id="3etoiles" name="note[]" ; value="3" />
                <label class="form-check-label" for="3etoiles">3 étoiles
                </label>
                <br />
                <input class="form-check-input" type="checkbox" id="2etoiles" name="note[]" ; value="2" />
                <label class="form-check-label" for="2etoiles">2 étoiles
                </label>
                <br />
                <input class="form-check-input" type="checkbox" id="1etoile" name="note[]" ; value="1" />
                <label class="form-check-label" for="1etoile">1 étoile
                </label>
                <button type="submit" id="boutonvalider">Valider</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </article>
</body>

</html>