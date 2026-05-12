<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard UniFix - Portale Guasti</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Skip link per accessibilità: permette di saltare il menu con la tastiera -->
    <a href="#main-content" class="skip-link">Vai al contenuto principale</a>

    <header role="banner">
        <div class="container header-flex">
            <h1>UniFix</h1>
            <nav aria-label="Menu principale">
                <ul>
                    <li><a href="index.php" aria-current="page">Home</a></li>
                    <li><a href="segnala.php">Invia Segnalazione</a></li>
                    <li><a href="profilo.php">Il mio Profilo</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main-content">
        <section class="hero container">
            <h2>Bentornato, [Nome Utente]</h2>
            <p>Controlla lo stato delle tue aule o segnala un nuovo problema.</p>
            
            <!-- Barra di Ricerca -->
            <form action="search.php" method="GET" class="search-box" role="search">
                <label for="search-aula" class="visually-hidden">Cerca aula o laboratorio</label>
                <input type="search" id="search-aula" name="q" placeholder="Cerca aula (es. Aula 101, Lab Inf...)">
                <button type="submit" class="btn-primary">Cerca</button>
            </form>

            <div class="quick-actions">
                <a href="segnala.html" class="btn-cta">⚠️ Segnala un Guasto</a>
            </div>
        </section>

        <section class="container">
            <h3>Le tue Aule Preferite</h3>
            <div class="grid-aule">
                <!-- Esempio di Card Aula -->
                <article class="card-aula">
                    <header class="card-header">
                        <h4>Aula Magna - Edificio A</h4>
                        <button aria-label="Rimuovi dai preferiti" class="btn-fav">★</button>
                    </header>
                    <div class="card-body">
                        <p class="status-ok">✅ Nessun guasto segnalato</p>
                    </div>
                    <footer class="card-footer">
                        <a href="aula-dettaglio.php?id=AM" class="btn-secondary">Vedi dettagli</a>
                    </footer>
                </article>

                <!-- Esempio di Aula con Guasto -->
                <article class="card-aula alert">
                    <header class="card-header">
                        <h4>Laboratorio 102 - Edificio C</h4>
                        <button aria-label="Rimuovi dai preferiti" class="btn-fav">★</button>
                    </header>
                    <div class="card-body">
                        <p class="status-warning">⚠️ 2 Segnalazioni attive</p>
                        <ul class="mini-list">
                            <li>Presa elettrica fila 3...</li>
                            <li>PC postazione 12...</li>
                        </ul>
                    </div>
                    <footer class="card-footer">
                        <a href="aula-dettaglio.php?id=L102" class="btn-secondary">Controlla guasti</a>
                    </footer>
                </article>
            </div>
        </section>
    </main>

    <footer role="contentinfo">
        <div class="container">
            <p>&copy; 2026 Università degli Studi - UniFix</p>
        </div>
    </footer>
</body>
</html>