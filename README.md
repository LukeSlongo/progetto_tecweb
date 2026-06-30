# Progetto UniFix - Istruzioni di Avvio

## Credenziali di Accesso
* **Amministratore:** admin / admin
* **Tecnico:** tecnico / tecnico
* **Utente Studente:** user / user

## Come avviare l'ambiente (tramite Docker)
Il progetto è containerizzato. Per avviare il server web Apache, il database MySQL e importare automaticamente i dati, esegui i seguenti comandi dal terminale nella directory radice del progetto:

1. Costruisci le immagini e avvia i container in background:
   `docker compose up --build -d`

2. Attendi qualche secondo per l'inizializzazione del database. L'applicativo sarà accessibile all'indirizzo:
   `http://localhost:8000`

## Importazione Manuale del Database (Alternativa)
Nel caso non si utilizzi Docker, il dump completo della struttura e dei dati è fornito nel file `dump_unifix.sql`. È sufficiente importare questo file nel proprio client MySQL locale (assicurandosi di avere un database chiamato `unifix`).
