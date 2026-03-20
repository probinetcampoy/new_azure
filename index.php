<?php
$apiUrl = "https://api-voyage-adam-ajdthba4evb9gqgb.francecentral-01.azurewebsites.net/destinations";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$destinations = [];
$error = null;

function fetchApiData($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return [false, $curlError ?: "HTTP $httpCode"];
        }

        return [$response, null];
    }

    $response = @file_get_contents($url);
    if ($response === false) {
        return [false, "Impossible de contacter l'API"];
    }

    return [$response, null];
}

list($response, $fetchError) = fetchApiData($apiUrl);

if ($response === false) {
    $error = "Impossible de charger les destinations : " . $fetchError;
} else {
    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        $error = "Réponse API invalide.";
    } else {
        $destinations = $decoded;

        if ($search !== '') {
            $destinations = array_filter($destinations, function ($dest) use ($search) {
                return isset($dest['name']) && stripos($dest['name'], $search) !== false;
            });
        }
    }
}

$contactSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $contactSuccess = true;
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TravelNow - Explorez le monde</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <header class="hero">
      <nav class="navbar">
        <div class="logo">TravelNow</div>
        <ul class="nav-links">
          <li><a href="#destinations">Destinations</a></li>
          <li><a href="#offres">Offres</a></li>
          <li><a href="#apropos">À propos</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </nav>

      <div class="hero-content">
        <h1>Partez à l’aventure avec TravelNow</h1>
        <p>Découvrez les plus belles destinations au meilleur prix.</p>
        <a href="#destinations" class="btn">Voir les voyages</a>
      </div>
    </header>

    <main>
      <section class="search-section">
        <h2>Rechercher une destination</h2>
        <form method="GET" action="">
          <input
            type="text"
            id="searchInput"
            name="search"
            placeholder="Ex : Bali, Tokyo, Rome..."
            value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
          />
        </form>
      </section>

      <section id="destinations" class="section">
        <h2>Nos destinations populaires</h2>

        <div class="cards-container">
          <?php if ($error): ?>
            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php elseif (empty($destinations)): ?>
            <p>Aucune destination trouvée.</p>
          <?php else: ?>
            <?php foreach ($destinations as $dest): ?>
              <div class="card" data-name="<?php echo htmlspecialchars($dest['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <img
                  src="<?php echo htmlspecialchars($dest['image_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars($dest['name'] ?? 'Destination', ENT_QUOTES, 'UTF-8'); ?>"
                />
                <div class="card-content">
                  <h3><?php echo htmlspecialchars($dest['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                  <p><?php echo htmlspecialchars($dest['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                  <span class="price">
                    À partir de <?php echo htmlspecialchars((string)($dest['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>€
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section id="offres" class="section offers">
        <h2>Offres spéciales</h2>
        <div class="offers-grid">
          <div class="offer-box">
            <h3>-20% sur les séjours d’été</h3>
            <p>
              Profitez de nos réductions exceptionnelles pour voyager en
              famille.
            </p>
          </div>
          <div class="offer-box">
            <h3>Week-end romantique</h3>
            <p>Des city-breaks à prix doux pour une escapade à deux.</p>
          </div>
          <div class="offer-box">
            <h3>Dernière minute</h3>
            <p>
              Réservez vite et partez dans les prochains jours à prix cassés.
            </p>
          </div>
        </div>
      </section>

      <section id="apropos" class="section about">
        <h2>À propos de nous</h2>
        <p>
          TravelNow est une plateforme de réservation de voyages qui aide les
          utilisateurs à trouver leur prochaine destination facilement. Notre
          objectif est de proposer une expérience simple, rapide et agréable.
        </p>
      </section>

      <section id="contact" class="section contact">
        <h2>Contact</h2>

        <?php if ($contactSuccess): ?>
          <p style="text-align:center; margin-bottom:20px;">
            Message envoyé avec succès !
          </p>
        <?php endif; ?>

        <form class="contact-form" method="POST" action="#contact">
          <input type="hidden" name="contact_form" value="1" />
          <input type="text" name="name" placeholder="Votre nom" required />
          <input type="email" name="email" placeholder="Votre email" required />
          <textarea name="message" rows="5" placeholder="Votre message" required></textarea>
          <button type="submit" class="btn">Envoyer</button>
        </form>
      </section>
    </main>

    <footer>
      <p>© 2026 TravelNow - Tous droits réservés</p>
    </footer>
  </body>
</html>