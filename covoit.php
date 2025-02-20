<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Covoiturage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
  <link rel="stylesheet" type="text/css" href="covoit.css" />
</head>

<body>
  <article>
    <div class="container-fluid ">
      <div class="poscovoit p-2">
        <div class="container-fluid">
          <div class="row">
            <div class="col-8 d-flex z-3">
              <h1 id="villedepart" data-category="depart">
                <?php echo $resultdepart ?>
              </h1>
              <img src="./arrow-right-solid.svg" class="p-2" width="10%"></img>
              <h1 id="villedarrive" data-category="arrive"><?php echo $resultarrivee?></h1>
            </div>
            <div class="col-1 posportrait">
              <img class="rounded" alt="portrait" />
            </div>
          </div>
        </div>
        <div class="container-fluid">
          <div class="row">
            <div class="col-6 d-flex pt-3">
              <h4 id="datecovoit"><?php echo $resultdate ?></h4>
            </div>
            <div class="col-3  ">
              <p class="pseudo"><?php echo $resultpseudo ?> </p>
            </div>

            <div class="container-fluid d-flex pt-4 ms-5">
              <h5 class="pe-2"><?php echo $resultheuredepart ?></h5>
              <img src="./timeline-solid.svg" width="8%" />
              <h5 class="ps-2"><?php echo $resultheurearrivee ?> </h5>
            </div>

            <div class="container-fluid">
              <div class="infocovoit">
                <div class="row">
                  <div class="col-3 padinfo text-center">
                    <h5><?php echo $trajetecologique ?></h5>
                  </div>
                  <div class="col-3 padinfo text-center">
                    <h5>Place : <?php echo $resultnbplace ?></h5>
                  </div>
                  <div class="col-3 padinfo text-center">
                    <h5><?php echo $resultprix ?> crédits</h5>
                  </div>
                </div>
                <div class="container-fluid">
                  <div class="row mx-auto">
                    <a href="detail.php?id=<?php echo $covoitid;?>" type="submit"> Pour en savoir plus...
                    </a></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  </article>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
  </script>
  <script src="scriptcovoit.js" type="module"> </script>
</body>

</html>