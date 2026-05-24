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
2. **visualizzazione dashboard**: il sistema mostra contenuti personalizzati in base al ruolo
    - Studente: aule preferite e loro stato
    - Tecnico: segnalazioni del proprio dipartimento
    - Admin: statistiche globali del sistema
3. **ricerca aule**: ricerca testuale di un'aula tramite nome aula. Il sistema mostra una lista di aule con
    - nome aula
    - edificio
    - piano
    - dipartimento
    - numero segnalazioni attive
4. **visualizza dettaglio aula**: l'utente seleziona un aula dall'elenco e il sistema mostra
    - info dell'aula (nome, piano, edificio)
    - segnalazioni attive riguardanti quell'aula
    - stato attuale

## Studente (Utente base)
1. **gestione preferiti**: aggiunta o rimozione di un aula specifica dalla propria lista preferiti
3. **apertura segnalazione**: compilazione del form per segnalare un nuovo guasto in un'aula specifica, inserendo titolo, descrizione e priorità
4. **visualizzazione proprie segnalazioni**: il sistema mostra tutte le segnalazioni create dallo studente con
   - titolo
   - aula
   - stato attuale
   - priorità
   - data di apertura
   - tecnico assegnato (se presente)
5. **visualizzazione dettaglio segnalazione**: lo studente segnala una segnalazione dalla propria lista segnalazioni e il sistema mostra i seguenti dettagli:
   - titolo
   - aula
   - stato attuale
   - priorità
   - data di apertura
   - tecnico assegnato (se presente)
   - descrizione


## Tecnico
1.  **Visualizzazione di coda lavoro**: accesso all'elenco di segnalazioni attive relative a edifici del proprio dipartimento di competenza. Per ogni segnalazione mostra:
    - titolo
    - aula ed edificio
    - priorità
    - stato
    - data apertura
    - tecnico assegnato (se in carico)
2.  **Visualizza dettaglio segnalazione**: il tecnico seleziona una segnalazione dalla coda e il sistema mostra tutti i dettagli completi e visualizza azioni disponibili in base al suo stato
3.  **Presa in carico**: assegnazione a sé stessi di una segnalazione in stato aperto -> la segnalazione diventa in stato in carico
4.  **Risoluzione guasto**: chiusura tecnica del ticket una volta completato il lavoro -> la segnalazione diventa risolta
5.  **Riassegnazione**: rimozione dell'incarico facendolo tornare in stato aperto


## Amministratore
1. **Gestione dipartimenti ed edifici**: operazioni CRUD su dipartimenti ed edifici
2. **Gestione Aule**: operazione CRUD su aule e mappatura su edifici
3. **Gestione Utenze**: eliminazione account Tecnico o Stuente
4. **Supervisione globale**: visualizzazione globala segnalazioni di tutti i dipartimenti