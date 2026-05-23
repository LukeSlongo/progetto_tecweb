# Attori del sistema:
- studente: Naviga il sistema, cerca aule, salva le aule tra i preferiti e apre nuove segnalazioni di guasto. Non è vincolato a un singolo dipartimento.
- tecnico: Personale addetto alla manutenzione, associato a uno specifico dipartimento. Prende in carico le segnalazioni, aggiorna lo stato dei lavori e li chiude a risoluzione avvenuta
- amministratore: Gestisce l'anagrafica del sistema (dipartimenti, edifici, aule, utenti) e supervisiona l'intero flusso di lavoro

# Casi d'uso

## Utente non autenticato (Ospite)
1. **registrazione**: creazione di un nuovo account con *nome_utente* e *password*
2. **login**: accesso al sistema tramite *nome_utente* e *password*

## Utente autenticato (azioni comuni)
1. **logout**: uscire dal sistema tramite tasto logout/esci
2. **consultazione dashboard**: visualizzazione dello stato delle aule preferite
3. **ricerca aule**: ricerca testuale di un'aula tramie nome aula

## Studente (Utente base)
3. **gestione preferiti**: aggiunta o rimozione di un aula specifica dalla propria lista preferiti
4. **apertura segnalazione**: compilazione del form per segnalare un nuovo guasto in un'aula specifica, inserendo titolo, descrizione e priorità
5. **monitoraggio proprie segnalazioni**: visualizzazione dell'elenco dei ticket aperti per controllarne l'avanzamento (aperto, in carico, risolto)


## Tecnico
1.  **Visualizzazione di coda lavoro**: accesso all'elenco di segnalazioni attive relative a edifici del proprio dipartimento di competenza
2.  **Presa in carico**: assegnazione a sé stessi di una segnalazione in stato aperto -> la segnalazione diventa in stato in carico
3.  **Risoluzione guasto**: chiusura tecnica del ticket una volta completato il lavoro -> la segnalazione diventa risolta
4.  **Riassegnazione**: rimozione dell'incarico facendolo tornare in stato aperto


## Amministratore
1. **Gestione dipartimenti ed edifici**: operazioni CRUD su dipartimenti ed edifici
2. **Gestione Aule**: operazione CRUD su aule e mappatura su edifici
3. **Gestione Utenze**: eliminazione account Tecnico o Stuente
4. **Supervisione globale**: visualizzazione globala segnalazioni di tutti i dipartimenti