# Attori del sistema:
- studente: Naviga il sistema, cerca aule, salva le aule tra i preferiti e apre nuove segnalazioni di guasto. Non è vincolato a un singolo dipartimento.
- tecnico: Personale addetto alla manutenzione, associato a uno specifico dipartimento. Prende in carico le segnalazioni, aggiorna lo stato dei lavori e li chiude a risoluzione avvenuta
- amministratore: Gestisce l'anagrafica del sistema (dipartimenti, edifici, aule, utenti) e supervisiona l'intero flusso di lavoro

# Casi d'uso

## Utente non autenticato (Ospite)
1. **registrazione**: creazione di un nuovo account con *nome e cognome*, *email* e *password*
2. **login**: accesso al sistema tramite *email* e *password*

## Utente autenticato (azioni comuni)
1. **logout**: uscire dal sistema tramite tasto logout/esci
2. **ricerca aule**: ricerca testuale di un'aula tramite nome aula, il sistema mostra una lista di aule.
3. **visualizza lista aule**: Il sistema mostra una lista di aule con
    - nome aula
    - edificio
    - piano
    - dipartimento
    - numero segnalazioni attive
4. **apertura segnalazione**: compilazione del form per segnalare un nuovo guasto in un'aula specifica, inserendo:
    - nome aula
    - piano aula
    - titolo segnalazione
    - descrizione segnalazione
    - priorità segnalazione

## Studente (Utente base)
1. **aggiunta di una aula ai preferiti**
2. **rimozione di una aula dai preferiti**
4. **visualizzazione lista segnalazioni**: il sistema mostra tutte le segnalazioni ancora non chiuse con
   - titolo segnalazione
   - aula segnalazione
   - data di apertura segnalazione
   - stato segnalazione
5. **visualizzazione dettaglio segnalazione**: lo studente seleziona una segnalazione e il sistema mostra i seguenti dettagli:
   - titolo segnalazione
   - aula segnalazione
   - data di apertura segnalazione
   - data di chiusura segnalazione (se chiusa)
   - stato segnalazione
   - priorità segnalazione
   - tecnico assegnato (se presente)
   - descrizione segnalazione
6. **visualizzazione dettaglio aula**: il sistema mostra il dettaglio dell'aula con i seguenti dati:
   - nome aula
   - piano aula
   - edificio aula
   - dipartimento aula
   - segnalazioni aula@
7. **visualizzazione dashboard**: il sistema mostra contenuti personalizzati
   - banner di ricerca
   - banner di segnalazione guasto 
   - lista aula preferite:
     - nome aula
     - edificio aula
     - piano aula
     - numero di segnalazioni proprie riguardanti quell'aula

## Tecnico
1.  **Visualizzazione di coda lavoro**: accesso all'elenco di segnalazioni attive relative a edifici del proprio dipartimento di competenza. Per ogni segnalazione mostra:
    - titolo segnalazione
    - aula
    - edificio
    - priorità
    - stato
    - data apertura
    - tecnico assegnato (se in carico)
2.  **Visualizza dettaglio segnalazione**: il tecnico seleziona una segnalazione dalla coda e il sistema mostra tutti i dettagli completi e visualizza azioni disponibili in base al suo stato
    - titolo segnalazione
    - aula
    - edificio
    - priorità
    - stato
    - data apertura
    - tecnico assegnato (se in carica)
    - [azione disponibile]
3.  **Presa in carico**: assegnazione a sé stessi di una segnalazione in stato aperto -> la segnalazione diventa in stato in carico
4.  **Risoluzione guasto**: chiusura tecnica del ticket una volta completato il lavoro -> la segnalazione diventa risolta
5.  **Riassegnazione**: rimozione dell'incarico facendolo tornare in stato aperto
7.  **visualizza dettaglio aula**: l'utente seleziona un aula dall'elenco e il sistema mostra
    - info dell'aula (nome, piano, edificio)
    - stato attuale (se ci sono segnalazioni oppure no)
    - lista di segnalazioni attive riguardanti quell'aula
8. **visualizzazione dashboard**: il sistema mostra i contenuti personalizzati
    - segnalazioni del proprio dipartimento (info su segnalatore, aula, titolo, descrizione, stato, priorità, data apertura, data chiusura)


## Amministratore
1. **Creazione dipartimento**: creazione di un dipartimento compilando un form con i seguenti dettagli:
    - nome dipartimento
2. **Aggiornamento dipartimento**: modifica di un dipartimento compilando un form precompilato con i seguenti dettagli:
    - nome dipartimento
3. **Rimozione dipartimento**: rimozione di un dipartimento selezionato
4. **Visualizzazione lista dipartimenti**: visualizzazione di una lista di tutti i dipartimenti con le seguenti informazioni:
    - nome dipartimento
    - azione dipartimento [modifica, elimina, aggiungi edifico]
5. **Aggiunta edificio a dipartimento**: si aggiunge un edificio compilando un form con i seguenti dati
    - nome edificio
    - indirizzo edificio
6. **Aggiornamento edificio**: modifica di un edificio compilando un form precompilato con i seguenti dettagli:
    - nome edificio
    - indirizzo edificio
7. **Rimozione edificio**: rimozione di un edifico selezionato
8. **Visualizzazione lista edifici di un dipartimento**: visualizzazione di una lista di edifici di un dipartimento, con le seguenti informazioni:
    - nome edificio
    - indirizzo edificio
    - azione edificio [modifica, elimina, aggiungi aula]
9.  **Aggiungi aula ad edificio**: si aggiunge un aula ad un determinato edificio compilando un form con i seguenti dati
    - nome aula
    - piano aula
10. **Aggiornamento aula**: modifica di un'aula compilando un form precompilato con i seguenti dettagli:
    - nome aula
    - piano aula
11. **Rimozione aula**: rimozione di un aula selezionata
12. **Visualizzazione lista aule di un edifico**: visualizzazione di una lista di aule di un edificio con le seguenti informazioni:
    - nome aula
    - piano aula
    - azione aula [modifica, elimina, aggiungi aula]
13. **Visualizzazione lista utenti**: visualizzazione di una lista di tutti gli account del sistema con le seguenti informazioni:
    - nome utente
    - ruolo utente
    - azione utente [rimuovi utente]
14. **Rimozione utente**: rimozione di un utente selezionato
