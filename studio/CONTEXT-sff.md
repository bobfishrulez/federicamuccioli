# Contesto: studio/sff.html — Studio di Fattibilità Apertura Libreria

## Cos'è
Pagina HTML statica standalone (no server, no build, no dipendenze esterne):
tutto CSS+JS inline in un unico file. Calcolatore di fattibilità generico per
l'apertura di una libreria indipendente, con un flusso di ricavo "servizi per
terzi" (eventi organizzati per altri soggetti + servizi orari tipo storytelling)
oltre alla vendita di libri. Non contiene nomi propri di persone o attività:
è pensato per essere riusabile da chiunque stia valutando un progetto simile,
con tutti i default espliciti come stime di mercato da sostituire con i dati
reali del progetto specifico.

## Come funziona
Un form con oltre 70 input (tutti con `id`, letti via JS in
`document.getElementById`) organizzato in 6 step. Ogni campo ha un
`<div class="help">` sotto la label che spiega cosa inserire e — dove c'è un
default — perché è stato scelto quel valore, con la fonte quando disponibile
(AIE, INPS, CCIAA, ARERA, Politecnico Milano, Ente Nazionale Microcredito).

1. **Tempo**: ore/settimana richieste per banco, amministrazione, eventi in
   libreria (organizzazione assorbibile nei momenti morti + svolgimento con
   allestimento/disallestimento non assorbibile), eventi per terzi
   (organizzazione + svolgimento, sempre extra), servizi esterni orari (parte
   pratica) e relativa parte commerciale (ricerca clienti). Il part-time non è
   una scelta manuale: si attiva in automatico quando le ore totali superano
   il massimo che chi gestisce l'attività è disposto a coprire
   (`ore_max_titolare_sett`), col relativo costo calcolato dalle ore
   necessarie × un costo orario impostabile. Si aggiunge sempre un costo di
   backup per le settimane di ferie/malattia, anche quando il carico
   settimanale regge da solo. Anche le entrate fisse già esistenti (Step 4)
   hanno un costo in ore (`ore_entrate_fisse_sett`): non sono "gratis" solo
   perché già acquisite, richiedono comunque tempo di mantenimento.
2. **Costi di avviamento**: spese una tantum (lavori, stock libri, stock/
   allestimento per prodotti alimentari e oggettistica se attivati, ecc.),
   quota coperta dal bando, capitale che serve avere subito, gap vs liquidità
   disponibile, rata mensile di un eventuale prestito/microcredito. I mq del
   locale determinano anche la capacità stimata di libri (confrontata nei
   risultati col primo assortimento) e — tramite uno script di auto-scaling —
   aggiornano in automatico canone, utenze, costo lavori e TARI in proporzione
   quando l'utente cambia il valore dei mq.
3. **Costi fissi mensili**: affitto, utenze, INPS, TARI, diritto camerale,
   POS, manutenzione, formazione, RC eventi, trasporto libri, costo orario
   part-time, ecc. + un buffer % di imprevisti sui costi ricorrenti.
4. **Ricavi**: libri, eventi in libreria, prodotti alimentari, abbonamenti
   book club, workshop, oggettistica, più una sezione "Servizi per terzi"
   (eventi per conto di altri soggetti + servizi orari) esplicitamente
   raggruppata perché è la stessa categoria di business, solo fatturata in
   due modi diversi. Eventi e workshop usano un tasso di riempimento (% della
   capienza) per il ricavo da biglietti, ma il costo delle materie prime è
   calcolato per persona × capienza piena (non × partecipanti attesi), perché
   si prepara per il pieno anche se non tutti i posti vengono venduti.
5. **Prudenza e proiezione nel tempo**: quanti mesi servono per arrivare dal
   mese di apertura al regime pieno di ricavi (rampa lineare), quando arriva
   il saldo del bando, e di quanto ridurre i ricavi per uno scenario prudente
   di confronto.
6. **Obiettivo**: quanto portare a casa netto **all'anno** (non al mese: il
   totale annuo è il vincolo, non la sua distribuzione mensile), aliquota
   fiscale, e una % di utile aggiuntivo da accantonare per la crescita oltre
   al semplice pareggio (rinnovo attrezzature, stock, buffer, espansione).

Al click su "Calcola" (`submit` con `e.preventDefault()`), il JS ricalcola
tutto client-side e inietta l'HTML dei risultati in `#risultati` (nessun
salvataggio, nessun reload, nessun invio dati altrove).

## Output principali
- **Doppio verdetto in libri/giorno**, non in euro, calcolato **a ritroso**
  (non stimato dal traffico): `libri_pareggio_giorno` = quanti libri/giorno
  servono per coprire il fabbisogno mensile che gli altri ricavi stimati
  (eventi, prodotti, abbonamenti, workshop, oggettistica, servizi per terzi,
  entrate fisse) non coprono già da soli; `libri_crescita_giorno` = lo stesso
  calcolo ma sul fabbisogno maggiorato della % di utile per la crescita
  (Step 6). Non c'è confronto con una "stima di traffico attuale": il numero
  di libri non è un input stimato ma l'output del calcolo. Se gli altri
  ricavi bastano già da soli, il target è 0 e il resto è avanzo.
- Se serve vendere libri (`libri_pareggio_giorno > 0`): due leve alternative
  (più eventi in libreria, più ore di servizi esterni) per coprire lo stesso
  importo senza contare sui libri.
- Tabella ricavi con colonna **quantità** oltre agli euro; per eventi/workshop
  la quantità mostra i partecipanti attesi (capienza × tasso di riempimento).
- Breakdown dei costi fissi: base + buffer imprevisti + part-time (continuativo
  + copertura assenze) + POS + rata prestito, invece di un unico aggregato.
- **Scenario prudente**: tabella di confronto Base vs scenario con ricavi
  ridotti della % impostata nello Step 5, ciascuno col proprio verdetto.
- **Tempo**: breakdown ore per voce (banco, amministrazione eccedente,
  svolgimento eventi in libreria, eventi/servizi per terzi), verdetto se serve
  part-time continuativo, e nota separata sul costo di backup per ferie/
  malattia (sempre presente, anche a carico orario sostenibile).
- Capitale iniziale: totale avviamento, contributo bando atteso (tetto
  24.000€), anticipo ottenibile con fideiussione, capitale necessario subito,
  gap vs liquidità, rata mensile prestito, più il confronto capacità del
  locale (mq × libri/mq) vs primo assortimento acquistato.
- **Proiezione liquidità nei primi 18 mesi**: applica la rampa di crescita
  dello Step 5 mese per mese, ricalcola le commissioni POS sulla base ridotta,
  aggiunge il contributo del bando al mese atteso, mostra la cassa cumulata a
  checkpoint (mesi 1,3,6,9,12,15,18) partendo dalla liquidità residua dopo
  l'apertura. Segnala il punto di minimo e se va sotto zero. Non include il
  prelievo personale — misura solo l'autosostenibilità di cassa dell'attività.

## Punti di attenzione se continui a modificarlo
- Nessun nome proprio (persone, locali, comuni) nel testo rivolto all'utente:
  è una scelta esplicita per tenere lo strumento generico e riusabile. Se
  aggiungi testo, evita di reintrodurne.
- I valori di default sono stime plausibili basate su ricerche di mercato
  2026 — NON sono dati definitivi, vanno sempre sostituiti con quelli reali
  del progetto specifico.
- Il regime fiscale assunto è forfettario (aliquota 5% agevolata primi 5 anni
  per nuove attività — DA VERIFICARE col commercialista in base ai requisiti).
- L'ammortamento (voce "Includere l'ammortamento") è un accantonamento
  prudenziale, non richiesto fiscalmente nel forfettario — toggle sì/no.
- Il gap di liquidità iniziale assume un prestito personale ammortizzato
  standard (formula rata = C·i/(1-(1+i)^-n)); non modella altre forme (es.
  crowdfunding, soci).
- Nessuna persistenza dati: ogni ricalcolo riparte dai valori nei campi del
  form al momento del click, non c'è localStorage.
- La rampa di crescita (Step 5) è **lineare** e si applica in modo uniforme a
  tutti i ricavi variabili (inclusi i servizi per terzi) — semplificazione
  dichiarata nel testo dei risultati. Vedi `frazione` nel loop
  `for (let m = 1; m <= MESI_PROIEZIONE; m++)`.
- La formula dell'anticipo su fideiussione (`min(0.5×spese_ammissibili,
  0.7×contributo_atteso)`) è un'euristica non verificata su un bando reale —
  da verificare col bando MiC ufficiale se si attiva la polizza fideiussoria.
- Il buffer sui costi fissi (Step 3) si applica solo a `cf_sola` (le voci
  "morbide"), non a part-time/POS/rata prestito, che sono già importi precisi.
- Ogni campo ha un `<div class="help">` — se aggiungi un campo nuovo,
  aggiungine uno anche per lui: è la convenzione di leggibilità del file.
- **Tabelle responsive senza scorrimento orizzontale**: sotto i 640px (media
  query in CSS) ogni `<tr>` diventa una scheda verticale e ogni `<td>` mostra
  come etichetta il testo del `<th>` della sua colonna, tramite l'attributo
  `data-label` (assegnato via JS, non scritto a mano nell'HTML). La funzione
  `applyResponsiveLabels()`, chiamata subito dopo `div.innerHTML = html`,
  legge la prima `<tr>` di ogni tabella come intestazione e assegna
  `data-label` a tutte le celle delle righe successive in base alla
  posizione di colonna. **Perché funzioni ogni tabella deve avere una vera
  riga di intestazione con `<th>`** e ogni riga dati deve avere lo stesso
  numero di celle della riga di intestazione (anche se una cella è vuota,
  va comunque inclusa come `<td></td>` per non sfalsare l'allineamento).
  Se aggiungi una nuova tabella nei risultati, ricordati la riga `<th>`.
- **Il ricavo libri non è un input, è un output**: non ci sono più i campi
  "persone al giorno" / "% che compra" — quel traffico-based estimate era
  ingannevole (per una libreria che deve ancora aprire non è dato reale, solo
  un'altra ipotesi da indovinare) e generava confusione. Ora `prezzo_medio_libro`
  e `margine_libro_perc` servono solo a convertire `fabbisogno_da_coprire_con_libri`
  in un numero di libri/giorno (`libri_pareggio_giorno`), che poi diventa
  `ricavo_libri_mese_target` = `Math.max(0, fabbisogno_da_coprire_con_libri)`
  e rientra in `ricavo_totale_stimato`. Per questo `base_incassi_pos` (usato
  per la commissione POS) esclude deliberatamente i libri: includerli
  creerebbe una dipendenza circolare (commissione sui libri → fabbisogno →
  target libri → commissione sui libri). L'incidenza omessa è marginale
  (~1% del target). Se reintroduci una stima di traffico in futuro, tienila
  separata dal target di pareggio, non sommarla: altrimenti si torna al
  doppio-conteggio/confusione che ha motivato questa modifica.
- **Eventi in libreria vs eventi per terzi**: sono due categorie di business
  distinte, non varianti della stessa voce. Gli eventi per terzi (organizzati
  per conto di un altro soggetto) sono trattati come la stessa categoria di
  ricavo dei servizi orari esterni (storytelling/consulenza) — raggruppati
  visivamente in Step 4 sotto "Servizi per terzi (esterni)" — perché entrambi
  sono lavoro pagato da qualcun altro, solo fatturato in modo diverso (a
  evento vs a ore). Se aggiungi un nuovo flusso "per conto terzi", mettilo in
  quella sezione, non tra i ricavi della libreria, ed escludilo da
  `base_incassi_pos` (i ricavi per terzi non passano dal POS della libreria).
- **Materie prime eventi**: il campo è a persona (`materie_prime_pp_evento_*`),
  moltiplicato per la **capienza massima** (`capienza_evento_*`), non per i
  partecipanti attesi — si prepara per il pieno anche se poi non si vende
  tutta la capienza. Il ricavo da biglietti invece usa i partecipanti attesi
  (capienza × tasso di riempimento). Sono quindi basi di calcolo diverse per
  la stessa voce "evento", intenzionalmente.
- **Ore di svolgimento evento** (sia libreria che terzi) includono
  esplicitamente allestimento + evento + disallestimento, non solo l'orario
  dell'evento stesso. **Ore di organizzazione** sono la preparazione nei
  giorni precedenti, concettualmente separata.
- **Part-time non è un input manuale**: `ore_parttime_necessarie` è derivato
  da `ore_totali_sett - ore_max_titolare_sett`. Il costo mensile
  (`costo_parttime_mens`) somma due componenti: copertura continuativa
  (`ore_parttime_necessarie × WEEKS × costo_orario_parttime`) e copertura
  delle assenze (`ore_totali_sett × settimane_assenza_annue × costo_orario_parttime
  / 12`), quest'ultima sempre presente anche quando non serve part-time
  continuativo. Se cambi la logica delle ore in Step 1, aggiorna entrambe.
- **Niente automatismi sui mq**: `mq_locale` non scrive né suggerisce più
  nulla nei campi di canone/utenze/lavori/TARI — sono tornati campi di
  libera compilazione, senza alcun collegamento visivo o automatico agli mq
  (due tentativi precedenti, auto-scrittura e poi suggerimento testuale
  live, sono stati entrambi rimossi su richiesta). L'unico uso rimasto dei
  mq è la capacità stimata di libri (`capacita_libri_stimata`).
- **Default prudenti, con eccezioni volute**: tassi di conversione/riempimento,
  margini e tempistiche di rampa sono orientati al ribasso (ricavi) o al
  rialzo (costi/buffer/tempi). Fanno eccezione le ore di organizzazione/
  svolgimento eventi (alzate, non abbassate: organizzare un evento richiede
  sempre più tempo del previsto) e le quantità di prodotti alimentari/
  oggettistica (alzate, perché vendute come regalo insieme ai libri, non
  come acquisto a sé — quindi trainate dal traffico libri, non un flusso
  indipendente da stimare al ribasso). Se aggiorni un default, non applicare
  la regola "sempre prudente" meccanicamente: chiediti se quella voce è
  davvero un rischio (ricavo incerto, costo sottostimato) o una dipendenza
  da un'altra voce già coperta altrove.
- **Tono singolare, non tecnico**: tutto il testo rivolto a chi compila si
  rivolge in seconda persona singolare ("tu", con accordi al femminile:
  "disposta", "sicura" ecc. — lo strumento è pensato per un'unica persona
  specifica), non con "voi" plurale né in forma impersonale. Evita anche
  linguaggio tecnico/implementativo (server, browser, "i dati non vengono
  salvati") nel testo utente: la privacy va detta in una riga semplice
  ("i tuoi dati restano privati"), il resto è dettaglio da developer, non
  da mostrare a chi sta per aprire un'attività.
- **Obiettivo annuo, non mensile**: Step 6 chiede `reddito_netto_annuo_target`
  (€/anno), non uno stipendio mensile — il conto economico mensile lo usa
  diviso per 12 come media (`stipendio_lordo_necessario`), ma il vincolo che
  l'utente esprime è sul totale annuo.
- **Costi di avviamento per prodotti alimentari/oggettistica**: i campi
  `costo_allestimento_prodotti`, `costo_stock_prodotti`,
  `costo_allestimento_oggettistica`, `costo_stock_oggettistica` vivono in
  Step 4 (vicino ai relativi toggle `vendi_prodotti`/`attiva_oggettistica`)
  ma vengono letti nel blocco JS di Step 2 e sommati al capitale di
  avviamento — sono condizionati dai rispettivi toggle (0 se disattivati). Le
  voci di allestimento (non lo stock) entrano anche in `capex_ammortizzabile`.

## Possibili prossimi step
- Aggiungere un pulsante "esporta risultati in PDF/CSV"
- Verificare con fonti ufficiali (bando MiC, commercialista, CCNL Commercio)
  le voci segnalate come "da verificare" sopra, prima di usare lo strumento
  per una domanda di bando reale
