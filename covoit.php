<form method="POST" action="" class="container mt-5" id="searchForm">
  <div class="row g-3">
    <div class="col-md-4">
      <label for="depart" class="form-label">Ville de départ</label>
      <input type="text" class="form-control" name="depart" id="depart" required>
    </div>
    <div class="col-md-4">
      <label for="destination" class="form-label">Ville d'arrivée</label>
      <input type="text" class="form-control" name="destination" id="destination" required>
    </div>
    <div class="col-md-4">
      <label for="date" class="form-label">Date</label>
      <input type="date" class="form-control" name="date" id="date" required>
    </div>
    <div class="col-12 text-center mt-3 mb-5">
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </div>
  </div>
</form>



<div class="container mt-4" id="resultsSection" style="display:none;">
  <div class="row">

    <div class="col-md-3">
      <div id="filterSection" style="display:none; border:1px solid #ccc; padding:20px; border-radius:10px;">
        <form id="filterForm">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="electrique" name="electrique" value="1">
            <label class="form-check-label" for="electrique" style="color:#6cbf84;">Voiture électrique
              uniquement</label>
          </div>
          <div class="mb-3">
            <label for="prix_max" class="form-label" style="color:#6cbf84;">Prix maximum :</label>
            <input type="number" class="form-control" id="prix_max" name="prix_max" min="0" placeholder="Ex: 40">
          </div>
          <div class="mb-3">
            <label for="duree_max" class="form-label" style="color:#6cbf84;">Durée maximale (en minutes) :</label>
            <input type="number" class="form-control" id="duree_max" name="duree_max" min="0" placeholder="Ex: 120">
          </div>
          <div class="mb-3">
            <label for="note_min" class="form-label" style="color:#6cbf84;">Note minimale du conducteur :</label>
            <input type="number" class="form-control" id="note_min" name="note_min" min="0" max="5" step="0.1"
              placeholder="Ex: 4.5">
          </div>
          <button type="submit" class="btn btn-success">Filtrer</button>
        </form>
      </div>
    </div>


    <div class="col-md-9">
      <div id="loadingSpinner" class="text-center my-4" style="display: none;">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>

      </div>
      <div class="container" id="resultsContainer"></div>
    </div>
    <!-- Zone où seront affichés les résultats -->

  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const searchForm = document.getElementById("searchForm");
  const filterForm = document.getElementById("filterForm");
  const resultsContainer = document.getElementById("resultsContainer");
  const filterSection = document.getElementById("filterSection");
  const spinner = document.getElementById("loadingSpinner");

  function hasActiveFilters() {
    const prix = document.getElementById("prix_max").value;
    const duree = document.getElementById("duree_max").value;
    const note = document.getElementById("note_min").value;
    const electrique = document.getElementById("electrique").checked;
    return prix || duree || note || electrique;
  }

  function fetchResults() {
    const formData = new FormData(searchForm);

    if (hasActiveFilters()) {
      const filterData = new FormData(filterForm);
      for (const [key, value] of filterData.entries()) {
        formData.append(key, value);
      }
    }

    spinner.style.display = "block";
    resultsContainer.innerHTML = "";
    document.getElementById("resultsSection").style.display = "block";

    fetch("search_api.php", {
        method: "POST",
        body: formData,
      })
      .then((res) => res.text())
      .then((html) => {
        spinner.style.display = "none";
        resultsContainer.innerHTML = html;
        filterSection.style.display = "block"; // Afficher la section des filtres
      })
      .catch((err) => {
        spinner.style.display = "none";
        resultsContainer.innerHTML = "<div class='alert alert-danger'>Une erreur s'est produite.</div>";
        console.error(err);
      });
  }

  // Soumission du formulaire principal
  searchForm.addEventListener("submit", function(e) {
    e.preventDefault();
    fetchResults();
  });

  // Soumission du formulaire de filtres — uniquement si un filtre est actif
  filterForm.addEventListener("submit", function(e) {
    e.preventDefault();
    if (hasActiveFilters()) {
      fetchResults();
    } else {
      alert("Aucun filtre actif !");
    }
  });
});
</script>