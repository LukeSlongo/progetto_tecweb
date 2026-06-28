#let titolo-progetto = "UniFix"
#let corso = "TECNOLOGIE WEB"
#let anno-accademico = "2025/2026"
#let universita = "UNIVERSITÀ DEGLI STUDI DI PADOVA"
#let dipartimento = "DIPARTIMENTO DI MATEMATICA"
#let corso-laurea = "LAUREA TRIENNALE IN INFORMATICA"

#let studenti = (
  (nome: "Enrique Hernandez Gris", matricola: "2169844"),
  (nome: "Marco Sanguin", matricola: "2103121"),
  (nome: "Luca Slongo", matricola: "2111009"),
  (nome: "Aldo Bettega", matricola: "2101087"),
)

#set page(
  paper: "a4",
  margin: (x: 2.5cm, y: 2.5cm),
  numbering: "1",
  header: context {
    if counter(page).get().first() > 1 {
      align(left, smallcaps(titolo-progetto))
      line(length: 100%, stroke: 0.5pt)
    }
  },
  footer: context {
    if counter(page).get().first() > 1 {
      align(center, counter(page).display("1"))
    }
  },
)

#set text(
  font: "New Computer Modern", // O "Linux Libertine"
  size: 12pt,
  lang: "it",
)

#set par(justify: true, leading: 0.65em)
#set heading(numbering: "1.")

// Stile blocchi codice
#show raw.where(block: true): block.with(
  fill: luma(245),
  inset: 10pt,
  radius: 4pt,
  width: 100%,
)


#align(center + horizon)[

  // LOGO
  #image("images/unipd.png", width: 40%)
  #v(1cm)

  #text(size: 14pt, smallcaps(universita)) \
  #v(0.5em)
  #text(size: 12pt, smallcaps(dipartimento)) \
  #v(0.5em)
  #text(size: 14pt, weight: "bold", smallcaps(corso-laurea)) \

  #v(3em)

  #text(size: 12pt, smallcaps(corso))

  #v(2em)
  #line(length: 100%, stroke: 1pt)
  #v(0.5em)

  // TITOLO GRANDE
  #text(size: 22pt, weight: "bold")[#titolo-progetto]

  #v(0.5em)
  #line(length: 100%, stroke: 1pt)

  #v(3fr)

  // --- SEZIONE CREDENZIALI E CONTATTI (SOSTITUISCE IL GRUPPO) ---
  #text(size: 12pt, weight: "bold")[Informazioni di Accesso e Contatto:]
  #v(1em)

  #align(center)[
    #grid(
      columns: (auto, auto),
      align: (right + horizon, left + horizon),
      // Allinea etichette a dx e valori a sx
      column-gutter: 1.5em,
      row-gutter: 1em,

      [*Utente Amministratore:*], [username: `admin`, password: `admin`],
      [*Utente Base:*], [username: `user`,  password: `user`],
      [*Indirizzo Sito:*], [#link("http://localhost:8000")[http://tecweb.studenti.math.unipd.it/msanguin]],
      [*Referente:*], [#link("mailto:aldo.bettega@studenti.unipd.it")[aldo.bettega\@studenti.unipd.it]],
    )
  ]
  // ---------------------------------------------------------------

  #v(2fr)

  #text(size: 12pt)[Anno Accademico #anno-accademico]
]

#pagebreak()

#show outline.entry: it => {
  v(12pt, weak: true)
  it
}
#outline(title: "Indice", indent: auto)
#pagebreak()


= Introduzione

== Contesto del problema
La gestione e la manutenzione delle infrastrutture universitarie, in particolare delle aule didattiche e dei laboratori, rappresenta una sfida organizzativa complessa.
Spesso, la comunicazione dei guasti (come strumentazione non funzionante, problemi di illuminazione o danni agli arredi) avviene in modo frammentato o tramite canali
non ottimizzati.
Questo genera non solo disagi per gli studenti e i docenti che vivono gli spazi, ma rallenta anche i tempi di intervento del personale tecnico,
ostacolato dalla mancanza di un sistema centralizzato per la ricezione e la gestione delle priorità.
Inoltre, molti degli strumenti digitali attualmente in uso peccano di scarsa inclusività, rendendo la segnalazione dei problemi non accessibile a tutti gli utenti.

== Soluzione
Per rispondere a questa esigenza nasce UniFix, una piattaforma web progettata con il duplice obiettivo di semplificare l'esposizione dei problemi
da parte degli utenti e di fornire uno strumento di back-office efficiente per l'amministrazione.
UniFix si propone come un ponte digitale tra chi vive gli spazi universitari e chi li gestisce, offrendo interfacce dedicate e ottimizzate per tre tipologie di attori:
lo studente (utente base), il tecnico manutentore e l'amministratore di sistema.

= Metodologia di lavoro
Lo sviluppo di UniFix ha richiesto una rigorosa organizzazione metodologica, necessaria per coordinare efficacemente il lavoro di un team operante in modalità
prevalentemente asincrona, a causa della concomitanza di altri impegni accademici e professionali (esami, tirocini).
Per garantire produttività costante, prevedibilità dell'andamento e un prodotto finale di alta qualità, il ciclo di vita del progetto è stato gestito applicando
principi mutuati dall'Ingegneria del Software, suddivisi nelle seguenti fasi chiave.

== Analisi e studio fattibilità
Il lavoro è iniziato con una approfondita fase di analisi del dominio, finalizzata a circoscrivere il problema originale e a delineare una soluzione tecnologica adeguata.
L'obiettivo primario è stato definire un perimetro di progetto che non solo rispettasse rigorosamente i requisiti accademici del corso,
ma che risultasse anche realistico e sostenibile in termini di tempistiche ed energie del team, garantendo la consegna di un prodotto concretamente in grado di risolvere
la problematica individuata.

== Progettazione e mockup visivo
Per fornire una visione chiara e condivisa dell'applicativo prima ancora di scrivere codice, è stato realizzato un prototipo interattivo (Mockup) utilizzando Figma.
Questa fase si è rivelata cruciale per:
- Immaginare l'interfaccia utente (UI) finale e definire la User Experience (UX).
- Identificare in anticipo le funzionalità necessarie e i dati che dovevano essere esposti a video, agevolando l'individuazione di potenziali barriere
  di accessibilità fin dal design. Parallelamente, è stata creata una struttura documentale centralizzata.
  Questa "Source of Truth" conteneva le specifiche di dominio, i diagrammi dei casi d'uso, i diagrammi delle classi e la mappa dei routing web.
  L'approvazione di questa documentazione ha evitato fraintendimenti e limitato drasticamente la necessità di successivi rimaneggiamenti del codice.

== Organizzazione del lavoro e gesitone dei task
La scomposizione del lavoro e il tracciamento delle attività sono stati gestiti tramite l'ecosistema GitHub, sfruttando una Project Board dedicata.
Tutte le funzionalità derivanti dall'analisi dei casi d'uso sono state tradotte a priori in specifiche "Issue" architetturali, contenenti il dettaglio delle classi,
dei metodi e dei template HTML necessari al loro completamento.
Questo approccio di pianificazione "up-front" ha permesso di:
- Stabilire le priorità di implementazione (Core features prima delle funzionalità aggiuntive).
- Parallelizzare lo sviluppo, evitando sovrapposizioni o conflitti sul codice ("pestarsi i piedi").
- Fornire metriche quantitative costanti sull'avanzamento dei lavori, agevolando la ricalibrazione delle energie del team.

== Flusso di lavoro e versioning
Per il controllo di versione è stato adottato un approccio basato sul Feature Branching.
- Ogni Issue è stata sviluppata all'interno di un branch isolato, mantenendo integro il branch principale di sviluppo (develop).
- L'integrazione del nuovo codice in develop avveniva esclusivamente tramite Pull Request. L'obbligo di Code Review da parte di altri membri del team
  ha garantito un controllo di qualità sistematico, elevando la stabilità e la completezza della baseline condivisa.

== Qualità del Codice e Continuous Integration
La fase di codifica è stata accompagnata dalla scrittura di test automatizzati (Unit Test). L'esecuzione di questi test è stata automatizzata tramite le GitHub Actions
al momento della Push/Pull Request.
Questo processo di integrazione continua (CI) ha permesso di monitorare il corretto funzionamento dei metodi chiave in modo rapido e autonomo,
offrendo la garanzia che l'aggiunta di nuovo codice (o il refactoring di codice esistente) non generasse regressioni su funzionalità già validate.
La sintesi di queste metodologie organizzative ha reso la fase di codifica rapida, efficiente e non dispersiva,
consentendo di reinvestire le risorse risparmiate in ampie sessioni di testing finale, con particolare focus sulla verifica dell'accessibilità dell'applicativo.

= Fasi del progetto

== Analisi dei Requisiti
La fase di analisi dei requisiti è stata fondamentale per definire il perimetro del progetto, identificare gli utenti target e stabilire le regole di business del dominio.
Attraverso un processo di elicitazione e raffinamento, sono stati definiti gli attori del sistema, i casi d'uso principali e i vincoli di integrità sui dati.

=== Attori del sistema
Il sistema UniFix prevede interfacce e permessi differenziati per tre tipologie di utenti:
- Studente (Utente Base): È il fruitore principale degli spazi universitari. Può navigare il sistema, cercare aule, monitorarne lo stato e aprire nuove segnalazioni
  di guasto. Dispone inoltre di un'area personale per salvare le aule tra i preferiti.
- Tecnico: È l'operatore incaricato della manutenzione. Ha il compito di supervisionare le segnalazioni aperte, prenderle in carico in totale autonomia
  e portarle a risoluzione.
- Amministratore (Admin): Ha un ruolo di supervisione globale. Gestisce le utenze del sistema (con facoltà di rimozione) e può intervenire in via eccezionale
  sull'eliminazione delle segnalazioni. Per semplicità progettuale, i profili di livello superiore (Admin e Tecnico) vengono pre-caricati nel sistema (hardcoded),
  mentre la registrazione è aperta solo agli utenti base.
=== Casi d'uso principali
+ Autenticazione: Registrazione (solo per studenti), Login e Logout.
+ Esplorazione e Preferiti: Ricerca testuale delle aule e visualizzazione del dettaglio dell'aula (con indicazione chiara dell'edificio e dell'indirizzo).
  Gli studenti possono aggiungere o rimuovere le aule da una propria lista di preferiti, visualizzabile direttamente in un carosello nella Home Page.
+ Apertura Segnalazione: Creazione di un nuovo ticket di guasto. Per ottimizzare la User Experience, la selezione del luogo del guasto è gestita
  in modo dinamico lato client (tramite JavaScript): selezionando un edificio, la tendina delle aule si filtra istantaneamente, o viceversa,
  selezionando un'aula viene auto-compilato l'edificio di appartenenza.
+ Gestione Interventi: I tecnici e gli admin accedono a una lista globale delle segnalazioni. Il tecnico può eseguire transizioni di stato del ticket
  (da "Aperto" a "In lavorazione", fino a "Chiuso").
+ Pannello Amministratore: L'admin può visualizzare la lista completa degli utenti registrati e procedere alla loro rimozione in caso di necessità,
  oltre a poter forzare l'eliminazione di qualsiasi issue.

== Architettura e Progettazione
La progettazione tecnica di UniFix è stata orientata alla creazione di un sistema robusto, manutenibile e performante.
Per dimostrare la padronanza delle architetture web moderne, il sistema è stato sviluppato interamente in PHP nativo (Vanilla PHP),
implementando da zero il design pattern Model-View-Controller (MVC), un sistema di routing avanzato e un motore di templating proprietario,
senza ricorrere a framework esterni.

=== Pattern MVC (Model-View-Controller)
L'applicativo è stato progettato secondo il principio della Separation of Concerns,
riflettendosi direttamente nella struttura gerarchica delle directory:

- Model (src/Models/): Contiene le classi dedicate all'astrazione del database MySQL. Estendendo una classe base Model.php,
  i modelli (es. IssueModel, UserModel) incapsulano le query SQL, garantendo la sicurezza contro le SQL Injection tramite
  l'utilizzo esclusivo di istruzioni preparate (Prepared Statements) fornite dall'estensione PDO.

- Controller (src/Controllers/): Ospita la logica di business. I controller (es. RoomController, IssueController)
  ricevono le richieste dal router, interrogano i Modelli necessari, elaborano i dati formattandoli correttamente per la UI
  e infine richiamano la View associata, iniettandovi i parametri.

- View (src/Views/): La componente visuale è stata suddivisa per favorire il riuso del codice. Le pages rappresentano le singole schermate
  (es. issueDetailPage.html), i layouts (main.html, auth.html) forniscono la struttura globale ricorrente, mentre i components
  contengono i frammenti riutilizzabili (es. le card o gli elementi delle liste).

=== Diagrammi delle classi
A monte della fase di implementazione vera e propria, l'architettura del software è stata rigorosamente modellata attraverso la stesura di diagrammi UML delle classi. Questa fase di progettazione a priori è stata fondamentale per mappare le responsabilità di ogni singolo componente, stabilire le relazioni tra di essi e definire un'interfaccia chiara per i metodi prima ancora di scrivere una singola riga di codice PHP.
L'intera organizzazione delle classi sfrutta ampiamente i paradigmi della programmazione orientata agli oggetti (OOP), facendo un uso massiccio dell'ereditarietà per garantire il rispetto del principio DRY e centralizzare la logica di base.

#image("images/diagramma_classi.png")

Nello specifico, la gerarchia è stata strutturata come segue:
- Il layer dei Controller: È stata definita una superclasse base Controller che implementa tutti i metodi trasversali necessari alla
  gestione delle richieste HTTP (come il recupero dei parametri get() e post()), le logiche di controllo degli accessi (es. requireLogin(),
  abort()) e i metodi di output verso la UI (render() e redirect()). Da questa superclasse ereditano i controller specifici di dominio
  (UserController, RoomController, IssueController), i quali si limitano a orchestrare le chiamate ai modelli e a definire la logica di
  business specifica per le relative rotte.
- Il layer dei Modelli: Analogamente, il livello di accesso ai dati è governato dalla superclasse base Model. Questa classe astratta
  incapsula la logica complessa di connessione al database (PDO) e fornisce metodi generici per l'esecuzione sicura delle query (query(),
  fetchOne(), fetchAll()) e le operazioni standard (come findById() o delete()). Le classi figlie (UserModel, RoomModel, IssueModel,
  BuildingModel) ereditano questi strumenti e li utilizzano per comporre le query SQL specifiche richieste dai casi d'uso (ad esempio
  getIssuesByRoom() o addFavorite())
- Classi di Supporto (Core/Helpers): Parallelamente alle classi MVC, sono state progettate a monte classi Core per la gestione di servizi
  trasversali, come la classe Auth, che centralizza la verifica dello stato della sessione e dei privilegi dell'utente (isLogged(),
  isAdmin(), isTechnician()) in modo sicuro e riutilizzabile da qualsiasi punto dell'applicativo.

=== Motore di tempalting
Per mantenere pulite le viste e isolare il PHP dall'HTML (limitando al minimo la presenza di logica condizionale nei file di presentazione),
è stata creata la classe core Template.php.
Questo mini-motore di templating si occupa di caricare i file HTML e di sostituire specifici placeholder (es. `##ROOM_NAME##`)
con i dati reali generati dal Controller, garantendo anche un corretto escaping dei caratteri (htmlspecialchars) per prevenire vulnerabilità XSS.

=== Routing e sicurezza
Il punto d'ingresso unico per l'intera applicazione (Front Controller) è il file public/index.php.
Questo script inizializza l'ambiente, registra l'autoloader per le classi e passa il controllo alla classe Router.php.
Il Router implementa un sistema avanzato di risoluzione delle URL basato su espressioni regolari (regex), permettendo la gestione
di rotte dinamiche (es. /issues/{id:num}).
Particolarmente rilevante è l'implementazione nativa di un sistema di Middleware per il controllo degli accessi a livello di rotta.
Prima di invocare il Controller, il Router verifica stringhe di autorizzazione associate alla singola rotta, quali:
- guest: Blocca l'accesso agli utenti già autenticati (es. pagina di login).
- auth: Richiede l'autenticazione tramite la classe helper Auth.php.
- admin / technician: Verifica il ruolo specifico dell'utente (RBAC - Role Based Access Control).
- owner:issue: Esegue un controllo a database per assicurarsi che l'utente che sta tentando un'azione distruttiva
  (es. l'eliminazione di una segnalazione) sia effettivamente il creatore (owner) della risorsa, a meno che non possieda privilegi di amministratore.

=== Persistenza dati

==== Progettazione del database e integrità dei dati
La persistenza dei dati è affidata a un database relazionale MySQL.
La progettazione dello schema concettuale (modello E/R) e logico è stata guidata dai principi di normalizzazione,
con l'obiettivo di garantire la consistenza delle informazioni ed evitare ridondanze.

#image("images/schema.png")

Il database si articola su cinque entità principali:
- Building e Room: Entità legate da una relazione uno-a-molti (1:N). Un edificio contiene più aule. Per evitare inconsistenze semantiche,
  è stato applicato un vincolo UNIQUE(building_id, name) sulla tabella room, garantendo che non possano esistere due aule con lo stesso nome all'interno del medesimo edificio.
- User: Tabella centralizzata per l'anagrafica, che distingue i privilegi tramite un attributo role di tipo ENUM ('student', 'technician', 'admin').
  Le password sono salvate sotto forma di hash crittografico sicuro.
- Issue: Entità centrale del sistema che traccia le segnalazioni. Mantiene relazioni in chiave esterna con room (l'aula guasta),
  user (lo studente segnalatore) e, opzionalmente, un secondo user (il tecnico assegnato).
- Favorite: Tabella di mapping per risolvere la relazione molti-a-molti (M:N) tra gli studenti e le aule preferite,
  avente come chiave primaria composita l'unione di user_id e room_id.

==== Gestione delle dipendenze e vincoli referenziali
Invece di demandare il controllo delle dipendenze alla logica applicativa (Backend PHP),
si è scelto di sfruttare i vincoli nativi del motore relazionale:
- Eliminazione in cascata (ON DELETE CASCADE): L'eliminazione di un edificio (building) comporta l'eliminazione automatica
  di tutte le sue aule (room). A sua volta, l'eliminazione di un'aula innesca la cancellazione a cascata di tutte le segnalazioni (issue)
  ad essa collegate e delle relative occorrenze nella tabella favorite.
- Mantenimento dello storico (ON DELETE SET NULL): Qualora un utente (studente o tecnico) venga rimosso dal sistema,
  i riferimenti testuali all'interno della tabella issue (user_id o technician_id) vengono impostati a NULL anziché eliminare
  l'intero record. Questo garantisce che lo storico degli interventi e dei guasti dell'Ateneo non venga perso.

==== Vincoli di dominio e logica procedurale
Per garantire che lo stato del sistema rispecchi sempre le regole di business definite in fase di analisi, sono stati implementati:
- Vincoli di Controllo (CHECK): Sulla tabella issue è attivo un vincolo che impedisce l'inserimento di una data di chiusura cronologicamente
  antecedente alla data di apertura del guasto (CHECK (closed_at IS NULL OR closed_at >= opened_at)).
- Trigger di transizione stato: È stato sviluppato un Trigger a livello di database (trg_remove_technician)
  che si attiva prima dell'eliminazione (BEFORE DELETE) di un record dalla tabella user. Se l'utente eliminato ha il ruolo di tecnico,
  il trigger interviene su tutte le segnalazioni a lui assegnate e attualmente "in lavorazione", rimuovendo l'assegnazione
  (technician_id = NULL) e riportando automaticamente lo stato della segnalazione su "Aperto" (status = 'open').
  Questo automatismo procedurale garantisce che nessun guasto rimanga "orfano" o bloccato nel sistema a seguito della rimozione del personale.

== Codifica

=== Implementazione Back-End (PHP)

==== Routing

==== Autenticazione

== Implementazione Front-End (HTML/CSS/JS)

= Accessibilità
aria, contrasti, tabelle mobile, responsive mobile, responsive print, tag aria, navigazione da tastiera, navigazione (submit con messaggio di ok), form con indicazioni corrette

== Test manuali

== Pull Request

== Branch Per feature

== Analisi dei requisiti, progettazione, codifica + testing, testing manuale + controllo accessibilità

== Suddivisione dei compiti

= Conclusioni e Sviluppi Futuri

== Competenze Acquisite

== Prospettive Future
