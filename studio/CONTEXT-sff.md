# Contesto: studio/sff.html — Studio di Fattibilità Libreria "Carta da Forno"

## Cos'è
Pagina HTML statica standalone (no server, no build, no dipendenze esterne):
tutto CSS+JS inline in un unico file. Serve a decidere se aprire una libreria
tematica (cultura del cibo + fotografia) a Longiano (FC), gestita da Federica,
con richiesta al bando MiC "Librerie Under 35" (scadenza 13/09/2026).

## Come funziona
Un form con ~62 input (tutti con `id`, letti via JS in `document.getElementById`)
organizzato in 6 step. Ogni campo ha un `<div class="help">` sotto la label che
spiega cosa inserire e — dove c'è un default — perché è stato scelto quel
valore (per renderlo compilabile da Federica senza bisogno di spiegazioni a voce):
1. **Tempo**: ore/settimana richieste (vendita, retrobottega, eventi, servizi
   esterni) vs soglia sostenibile per una persona sola → dice se serve un part-time.
2. **Costi di avviamento**: spese una tantum (lavori, stock libri, ecc.),
   quota coperta dal bando, capitale che serve avere subito, gap vs liquidità
   disponibile, rata mensile di un eventuale prestito/microcredito per coprire
   quel gap.
3. **Costi fissi mensili**: affitto, utenze, INPS, TARI, diritto camerale,
   POS, manutenzione, formazione, RC eventi, trasporto libri, ecc. + un buffer
   % di imprevisti sui costi ricorrenti (voci NUOVO aggiunte in revisione).
4. **Ricavi**: 7 flussi (libri, eventi, prodotti alimentari, abbonamenti book
   club, workshop, oggettistica, servizi professionali esterni di Federica) +
   entrate fisse già esistenti. Eventi e workshop ora usano un **tasso di
   riempimento** (% della capienza) invece di assumere il tutto esaurito; le
   ore di servizi esterni hanno una **% fatturabile** (non tutte le ore sono
   fatturate a un cliente).
5. **Prudenza e proiezione nel tempo** (NUOVO step): quanti mesi servono per
   arrivare dal mese di apertura al regime pieno di ricavi (rampa lineare),
   quando arriva il saldo del bando, e di quanto ridurre i ricavi per uno
   scenario prudente di confronto.
6. **Obiettivo**: stipendio netto target ed aliquota fiscale.

Al click su "Calcola" (`submit` con `e.preventDefault()`), il JS ricalcola
tutto client-side e inietta l'HTML dei risultati in `#risultati` (nessun
salvataggio, nessun reload, nessun invio dati altrove).

## Output principali
- Verdetto a regime: il piano regge o manca X €/mese (con stipendio NETTO
  trasformato in LORDO dividendo per (1 − aliquota fiscale), aliquota
  forfettario agevolato default 5%)
- Se non regge: le 3 leve alternative per chiudere il gap (più libri/giorno,
  più eventi/mese, più ore di servizi esterni)
- Tabella ricavi con colonna **quantità** oltre agli euro (es. "3,2 libri/
  giorno", non solo "180€/mese"); per eventi/workshop la quantità mostra i
  partecipanti attesi (capienza × tasso di riempimento), non la capienza piena
- Breakdown dei costi fissi: base + buffer imprevisti + part-time + POS + rata
  prestito, invece di un unico numero aggregato
- **Scenario prudente** (NUOVO): tabella di confronto Base vs scenario con
  ricavi ridotti della % impostata nello Step 5, ciascuno col proprio verdetto
- Verifica tempo (ore totali vs soglia sostenibile)
- Capitale iniziale: totale avviamento, contributo bando atteso (tetto
  24.000€), anticipo ottenibile con fideiussione, capitale necessario subito,
  gap vs liquidità, rata mensile prestito
- **Proiezione liquidità nei primi 18 mesi** (NUOVO): applica la rampa di
  crescita dello Step 5 mese per mese, ricalcola le commissioni POS sulla base
  ridotta, aggiunge il contributo del bando al mese atteso, e mostra la cassa
  cumulata a checkpoint (mesi 1,3,6,9,12,15,18) partendo dalla liquidità
  residua dopo l'apertura. Segnala il punto di minimo e se va sotto zero.
  Non include il prelievo dello stipendio personale (dichiarato esplicitamente
  nel testo dei risultati) — misura solo l'autosostenibilità di cassa
  dell'attività.

## Punti di attenzione se continui a modificarlo
- I valori di default nei campi sono stime plausibili basate su ricerche web
  di luglio 2026 (AIE per prezzo libri, INPS circolare 14/2026 per contributi,
  CCIAA per diritto camerale, ARERA/dati locali per TARI, Politecnico Milano
  per commissioni POS, tassi microcredito Ente Nazionale Microcredito) — NON
  sono dati definitivi, vanno sostituiti con quelli reali di Federica.
- Il regime fiscale assunto è forfettario (aliquota 5% agevolata primi 5 anni
  per nuove attività — DA VERIFICARE col commercialista se Federica rientra
  nei requisiti).
- L'ammortamento (voce "Includere l'ammortamento") è un accantonamento
  prudenziale, non richiesto fiscalmente nel forfettario (che non deduce
  costi reali) — c'è un toggle sì/no per includerlo o no nel calcolo finale.
- Il gap di liquidità iniziale assume che l'unica fonte di finanziamento sia
  un prestito personale amortizzato standard (formula rata = C·i/(1-(1+i)^-n));
  non modella altre forme (es. crowdfunding, soci).
- Nessuna persistenza dati: ogni ricalcolo riparte dai valori nei campi del
  form al momento del click, non c'è localStorage (volutamente, per restare
  compatibile ovunque il file venga aperto).
- La rampa di crescita (Step 5) è **lineare** dalla % del mese 1 al 100% al
  mese `mesi_rampa`, e si applica in modo uniforme a tutti i ricavi variabili
  (inclusi i servizi esterni di Federica) — semplificazione dichiarata nel
  testo dei risultati, non un limite nascosto.
  Vedi `frazione` nel loop `for (let m = 1; m <= MESI_PROIEZIONE; m++)`.
- La formula dell'anticipo su fideiussione (`min(0.5×spese_ammissibili,
  0.7×contributo_atteso)`) è un'euristica non verificata su un bando reale —
  a differenza degli altri default non ha una fonte citata. Da verificare col
  bando MiC ufficiale se si attiva la polizza fideiussoria.
- Il buffer sui costi fissi (Step 3) si applica solo a `cf_sola` (le voci
  "morbide": utenze, INPS, manutenzione, ecc.), non a part-time/POS/rata
  prestito, che sono già importi precisi noti dall'utente.
- Ogni campo ora ha un `<div class="help">` — se aggiungi un campo nuovo,
  aggiungine uno anche per lui: è la convenzione di leggibilità del file.

## Possibili prossimi step
- Aggiungere un pulsante "esporta risultati in PDF/CSV"
- Rendere responsive la tabella su mobile (le tabelle hanno già
  `overflow-x:auto`, ma con molte colonne — es. proiezione liquidità — su
  schermi stretti resta comunque da scorrere orizzontalmente)
- Eventualmente collegarlo a un foglio di calcolo condiviso per Federica
- Verificare con fonti ufficiali (bando MiC, commercialista) le voci segnalate
  come "da verificare" sopra, prima di usare lo strumento per la domanda al
  bando
