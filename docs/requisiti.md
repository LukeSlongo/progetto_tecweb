# Attori del sistema:
- studente: Naviga il sistema, cerca aule, salva le aule tra i preferiti e apre nuove segnalazioni di guasto. Non è vincolato a un singolo dipartimento.
- amministratore: Gestisce l'anagrafica del sistema (dipartimenti, edifici, aule, utenti), supervisiona l'intero flusso di lavoro e aggiorna lo stato dei lavori e li chiude a risoluzione avvenuta. 

# Casi d'uso

## Utente non autenticato (Ospite)
1. **registrazione**: creazione di un nuovo account con *nome utente* e *password*
2. **login**: accesso al sistema tramite *nome utente* e *password*

## Utente autenticato (azioni comuni)
1. **logout**: uscire dal sistema tramite tasto logout/esci
2. **ricerca aule**: ricerca testuale di un'aula tramite nome aula, il sistema mostra una lista di aule.
3. **visualizza lista aule**: Il sistema mostra una lista di aule con
    - nome aula
    - edificio
    - numero segnalazioni attive
4. **visualizzazione dettaglio aula**: il sistema mostra il dettaglio dell'aula con i seguenti dati:
   - nome aula
   - edificio aula
   - segnalazioni aula (titolo, data apertura, stato segnalazione)
5. **creazione segnalazione**: compilazione del form per segnalare un nuovo guasto in un'aula specifica, inserendo:
    - edificio 
    - nome aula
    - titolo segnalazione
    - descrizione segnalazione
    - priorità segnalazione
6.  **visualizza dettaglio segnalazione**: seleziona una segnalazione dalla coda e il sistema mostra tutti i dettagli completi e visualizza azioni disponibili in base al suo stato
    - titolo segnalazione
    - aula
    - edificio
    - stato
    - data apertura/chiusura
    - _(solo per admin)_ utente segnalatore

## Studente (Utente base)
1. **aggiunta di una aula ai preferiti**
2. **rimozione di una aula dai preferiti**

## Amministratore

1.  **Visualizzazione lista segnalazioni**: accesso all'elenco di segnalazioni attive. Per ogni segnalazione mostra:
    - titolo segnalazione
    - aula
    - edificio
    - stato
    - data apertura/chiusura
    
2.  **Risoluzione guasto**: chiusura tecnica del ticket una volta completato il lavoro -> la segnalazione diventa chiusa

3. **Visualizzazione lista utenti**: visualizzazione di una lista di tutti gli account del sistema con le seguenti informazioni:
    - nome utente
    - ruolo utente
    - azione utente [rimuovi utente]
4. **Rimozione utente**: rimozione di un utente selezionato
