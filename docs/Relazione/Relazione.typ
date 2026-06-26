#let titolo-progetto = "EasyGuitar - Accordi accessibili"
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
      align: (right + horizon, left + horizon), // Allinea etichette a dx e valori a sx
      column-gutter: 1.5em,
      row-gutter: 1em,

      [*Utente Amministratore:*], [username: `admin`, password: `admin`],
      [*Utente Base:*],           [username: `user`,  password: `user`],
      [*Indirizzo Sito:*],        [#link("http://localhost:8000")[http://tecweb.studenti.math.unipd.it/msanguin]],
      [*Referente:*],             [#link("mailto:aldo.bettega@studenti.unipd.it")[aldo.bettega\@studenti.unipd.it]]
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

= Metodologia di lavoro
test, bpf, pr, action,deploy, docker

= Fasi del progetto

== Analisi dei Requisiti

=== use case

==== utente base

==== tecnico

==== admin

== Architettura e Progettazione

L'architettura del sistema è stata definita con l'obiettivo di garantire modularità, manutenibilità e sicurezza. Questa decisione didattica ha permesso di avere il pieno controllo sul ciclo di vita della richiesta HTTP e di implementare manualmente i pattern architetturali fondamentali.

=== Pattern MVC (Model-View-Controller)


=== Struttura delle Directory
La struttura del progetto riflette la separazione logica del pattern MVC, mantenendo isolato il codice sorgente (`src`) dalle risorse accessibili pubblicamente (`public`).

#block(
  fill: luma(245),
  inset: 10pt,
  radius: 4pt,
  width: 100%,
  [
    ```text
├── public
│   ├── css
│   ├── img
│   ├── index.php
│   └── js
└── src
    ├── Controllers
    │   ├── Api
    │   └── Base
    ├── Core
    ├── Exceptions
    ├── Helpers
    ├── Models
    └── Views
        ├── components
        ├── layouts
        └── pages 

    ```
  ]
)

=== Database

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
