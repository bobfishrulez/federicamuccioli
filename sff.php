<?php
/* =========================================================================
   STUDIO DI FATTIBILITA' - Carta da Forno / Libreria Federica
   Pagina singola: form di input + motore di calcolo in PHP puro.
   Nessun database: ogni invio ricalcola tutto da zero (self-submitting form).
   ========================================================================= */

function num($key, $default = 0) {
    if (isset($_POST[$key]) && $_POST[$key] !== '') {
        return (float) str_replace(',', '.', $_POST[$key]);
    }
    return $default;
}
function yn($key) {
    return isset($_POST[$key]) && $_POST[$key] === 'si';
}
function val($key, $default = '') {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : $default;
}
function sel($key, $option) {
    return (isset($_POST[$key]) && $_POST[$key] === $option) ? 'selected' : '';
}
function euro($n) {
    return number_format($n, 0, ',', '.') . ' €';
}
function euro2($n) {
    return number_format($n, 2, ',', '.') . ' €';
}

$WEEKS = 4.33; // settimane medie per mese, usato in tutte le conversioni sett->mese

$calcolato = isset($_POST['calcola']);
$risultati = [];

if ($calcolato) {

    // ---------- STEP 1: TEMPO ----------
    $giorni_apertura        = num('giorni_apertura');
    $ore_apertura_giorno    = num('ore_apertura_giorno');
    $ore_retrobottega       = num('ore_retrobottega');
    $eventi_mese            = num('eventi_mese');
    $ore_per_evento         = num('ore_per_evento');
    $ore_servizi_esterni    = num('ore_servizi_esterni');
    $assumere               = yn('assumere');
    $ore_parttime           = $assumere ? num('ore_parttime') : 0;
    $soglia_ore             = num('soglia_ore', 45);

    $ore_vendita_sett  = $giorni_apertura * $ore_apertura_giorno;
    $ore_eventi_sett   = ($eventi_mese * $ore_per_evento) / $WEEKS;
    $ore_totali_sett   = $ore_vendita_sett + $ore_retrobottega + $ore_eventi_sett + $ore_servizi_esterni;
    $ore_federica_sett = $ore_totali_sett - $ore_parttime;

    $tempo_sostenibile = $ore_federica_sett <= $soglia_ore;
    $ore_parttime_necessarie = max(0, $ore_totali_sett - $soglia_ore);

    // ---------- STEP 2: COSTI DI AVVIAMENTO ----------
    $mq_locale          = num('mq_locale');
    $canone_mensile     = num('canone_mensile');
    $deposito_mesi      = num('deposito_mesi');
    $costo_lavori       = num('costo_lavori');
    $costo_insegna      = num('costo_insegna');
    $costo_software_setup = num('costo_software_setup');
    $costo_stock_iniziale = num('costo_stock_iniziale');
    $perc_conto_deposito  = num('perc_conto_deposito');
    $costo_notaio_comm    = num('costo_notaio_comm');
    $costo_polizza        = num('costo_polizza');
    $perc_ammissibile_bando = num('perc_ammissibile_bando', 100);
    $perc_imprevisti      = num('perc_imprevisti', 10);
    $liquidita_disponibile = num('liquidita_disponibile');

    $costo_stock_immediato = $costo_stock_iniziale * (1 - $perc_conto_deposito / 100);
    $deposito_cauzionale   = $canone_mensile * $deposito_mesi;

    $subtotale_avviamento = $deposito_cauzionale + $costo_lavori + $costo_insegna
                          + $costo_software_setup + $costo_stock_immediato + $costo_notaio_comm;

    $subtotale_con_imprevisti = $subtotale_avviamento * (1 + $perc_imprevisti / 100);
    $totale_avviamento = $subtotale_con_imprevisti + $costo_polizza;

    $spese_ammissibili_bando = $subtotale_con_imprevisti * ($perc_ammissibile_bando / 100);
    $contributo_bando_atteso = min(24000, $spese_ammissibili_bando);

    $anticipo_ottenibile = 0;
    if ($costo_polizza > 0) {
        $anticipo_ottenibile = min(0.5 * $spese_ammissibili_bando, 0.7 * $contributo_bando_atteso);
    }

    $capitale_necessario_subito = $totale_avviamento - $anticipo_ottenibile;
    $gap_liquidita = $capitale_necessario_subito - $liquidita_disponibile;

    // ---------- STEP 2: COSTI FISSI MENSILI ----------
    $utenze_mensili       = num('utenze_mensili');
    $telefono_mensile     = num('telefono_mensile');
    $assicurazione_mensile= num('assicurazione_mensile');
    $software_abbon       = num('software_abbon');
    $commercialista_mens  = num('commercialista_mens');
    $contributi_inps      = num('contributi_inps');
    $costo_parttime_mens  = $assumere ? num('costo_parttime_mens') : 0;

    $cf_sola      = $canone_mensile + $utenze_mensili + $telefono_mensile + $assicurazione_mensile
                  + $software_abbon + $commercialista_mens + $contributi_inps;
    $cf_scelto    = $cf_sola + $costo_parttime_mens;

    // ---------- STEP 3: RICAVI ----------
    $prezzo_medio_libro   = num('prezzo_medio_libro');
    $margine_libro_perc   = num('margine_libro_perc', 32);
    $persone_giorno       = num('persone_giorno');
    $perc_acquisto        = num('perc_acquisto');

    $prezzo_evento        = num('prezzo_evento');
    $capienza_evento      = num('capienza_evento');
    $perc_trattenuta_demetra = num('perc_trattenuta_demetra', 100);
    $materie_prime_evento = num('materie_prime_evento');

    $vendi_prodotti       = yn('vendi_prodotti');
    $prodotti_al_giorno   = num('prodotti_al_giorno');
    $scontrino_prodotto   = num('scontrino_prodotto');
    $margine_prodotto_perc= num('margine_prodotto_perc');

    $attiva_abbonamento   = yn('attiva_abbonamento');
    $periodicita_abbon    = val('periodicita_abbon', 'mensile');
    $prezzo_abbonamento   = num('prezzo_abbonamento');
    $numero_abbonati      = num('numero_abbonati');

    $attiva_workshop      = yn('attiva_workshop');
    $prezzo_workshop      = num('prezzo_workshop');
    $capienza_workshop    = num('capienza_workshop');
    $workshop_al_mese     = num('workshop_al_mese');

    $attiva_oggettistica  = yn('attiva_oggettistica');
    $scontrino_oggetti    = num('scontrino_oggetti');
    $margine_oggetti_perc = num('margine_oggetti_perc');
    $oggetti_al_mese      = num('oggetti_al_mese');

    $tariffa_servizi_esterni = num('tariffa_servizi_esterni');
    $entrate_fisse_esistenti = num('entrate_fisse_esistenti');

    $stipendio_target = num('stipendio_target');

    // giorni di apertura al mese
    $giorni_apertura_mese = $giorni_apertura * $WEEKS;

    // margine unitario libro
    $margine_unitario_libro = $prezzo_medio_libro * ($margine_libro_perc / 100);

    // ricavo organico da traffico
    $ricavo_libri_mese = $persone_giorno * ($perc_acquisto / 100) * $margine_unitario_libro * $giorni_apertura_mese;

    // margine netto per evento e ricavo mensile eventi
    $margine_evento_unitario = (($prezzo_evento * $capienza_evento) - $materie_prime_evento) * ($perc_trattenuta_demetra / 100);
    $ricavo_eventi_mese = $eventi_mese * $margine_evento_unitario;

    // prodotti alimentari
    $ricavo_prodotti_mese = 0;
    if ($vendi_prodotti) {
        $ricavo_prodotti_mese = $prodotti_al_giorno * $scontrino_prodotto * ($margine_prodotto_perc / 100) * $giorni_apertura_mese;
    }

    // abbonamenti
    $ricavo_abbonamenti_mese = 0;
    if ($attiva_abbonamento) {
        $prezzo_mensile_equiv = ($periodicita_abbon === 'annuale') ? ($prezzo_abbonamento / 12) : $prezzo_abbonamento;
        $ricavo_abbonamenti_mese = $numero_abbonati * $prezzo_mensile_equiv;
    }

    // workshop
    $ricavo_workshop_mese = 0;
    if ($attiva_workshop) {
        $ricavo_workshop_mese = $workshop_al_mese * $prezzo_workshop * $capienza_workshop;
    }

    // oggettistica
    $ricavo_oggetti_mese = 0;
    if ($attiva_oggettistica) {
        $ricavo_oggetti_mese = $oggetti_al_mese * $scontrino_oggetti * ($margine_oggetti_perc / 100);
    }

    // servizi esterni
    $ricavo_servizi_mese = $tariffa_servizi_esterni * $ore_servizi_esterni * $WEEKS;

    $ricavo_totale_stimato = $ricavo_libri_mese + $ricavo_eventi_mese + $ricavo_prodotti_mese
                           + $ricavo_abbonamenti_mese + $ricavo_workshop_mese + $ricavo_oggetti_mese
                           + $ricavo_servizi_mese + $entrate_fisse_esistenti;

    // ---------- STEP 4: VERIFICA ----------
    $fabbisogno_mensile = $cf_scelto + $stipendio_target;
    $differenza = $ricavo_totale_stimato - $fabbisogno_mensile;
    $piano_regge = $differenza >= 0;

    $gap_mensile = max(0, -$differenza);
    $libri_giorno_extra = ($margine_unitario_libro > 0 && $giorni_apertura_mese > 0)
        ? ($gap_mensile / $margine_unitario_libro) / $giorni_apertura_mese : null;
    $eventi_extra_mese = ($margine_evento_unitario > 0)
        ? $gap_mensile / $margine_evento_unitario : null;
    $ore_extra_servizi_sett = ($tariffa_servizi_esterni > 0)
        ? ($gap_mensile / $WEEKS) / $tariffa_servizi_esterni : null;

    // libri/giorno "totali" impliciti nel ricavo stimato (utile come riferimento)
    $libri_giorno_impliciti = ($margine_unitario_libro > 0 && $giorni_apertura_mese > 0)
        ? ($ricavo_libri_mese / $margine_unitario_libro) / $giorni_apertura_mese : 0;

    $risultati = compact(
        'ore_vendita_sett','ore_eventi_sett','ore_totali_sett','ore_federica_sett',
        'tempo_sostenibile','soglia_ore','ore_parttime_necessarie','assumere','ore_parttime',
        'totale_avviamento','subtotale_con_imprevisti','costo_polizza','spese_ammissibili_bando',
        'contributo_bando_atteso','anticipo_ottenibile','capitale_necessario_subito',
        'liquidita_disponibile','gap_liquidita',
        'cf_sola','cf_scelto','costo_parttime_mens',
        'ricavo_libri_mese','ricavo_eventi_mese','ricavo_prodotti_mese','ricavo_abbonamenti_mese',
        'ricavo_workshop_mese','ricavo_oggetti_mese','ricavo_servizi_mese','entrate_fisse_esistenti',
        'ricavo_totale_stimato','fabbisogno_mensile','differenza','piano_regge','gap_mensile',
        'libri_giorno_extra','eventi_extra_mese','ore_extra_servizi_sett','libri_giorno_impliciti',
        'stipendio_target'
    );
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Studio di Fattibilità — Libreria Carta da Forno</title>
<style>
  :root{
    --verde:#7A9E7E; --arancio:#C08552; --blu:#6B7FA3; --rosso:#A3596B;
    --bg:#FAF8F5; --testo:#2B2B2B; --bordo:#DDD8CE;
  }
  *{box-sizing:border-box;}
  body{
    font-family:'Georgia', 'Segoe UI', sans-serif;
    background:var(--bg); color:var(--testo);
    max-width:900px; margin:0 auto; padding:24px 16px 80px;
    line-height:1.5;
  }
  h1{font-size:1.6rem; border-bottom:3px solid var(--arancio); padding-bottom:10px;}
  h2{
    font-size:1.15rem; margin-top:36px; color:#fff;
    background:var(--blu); padding:8px 14px; border-radius:6px 6px 0 0;
  }
  fieldset{
    border:1px solid var(--bordo); border-top:none; border-radius:0 0 6px 6px;
    padding:16px; margin:0 0 4px 0; background:#fff;
  }
  legend{display:none;}
  .campo{margin-bottom:12px; display:flex; flex-wrap:wrap; align-items:center; gap:10px;}
  label{flex:1 1 380px; font-size:0.94rem;}
  .nuovo{color:var(--rosso); font-weight:bold; font-size:0.8rem;}
  input[type=number], input[type=text], select{
    padding:7px 9px; border:1px solid #BBB; border-radius:5px; font-size:0.95rem;
    width:150px;
  }
  .btn{
    background:var(--rosso); color:#fff; border:none; padding:14px 28px;
    font-size:1.05rem; border-radius:6px; cursor:pointer; margin-top:24px;
  }
  .btn:hover{opacity:0.9;}
  .risultati{
    background:#fff; border:2px solid var(--verde); border-radius:8px;
    padding:20px 22px; margin-bottom:30px;
  }
  .risultati.negativo{border-color:var(--rosso);}
  .verdetto{
    font-size:1.3rem; font-weight:bold; padding:12px; border-radius:6px; margin-bottom:16px;
  }
  .verdetto.ok{background:#E5F0E6; color:#3A5C3D;}
  .verdetto.no{background:#F3E3E7; color:#7A3040;}
  table{width:100%; border-collapse:collapse; margin:10px 0 20px;}
  td, th{padding:6px 8px; border-bottom:1px solid var(--bordo); font-size:0.93rem; text-align:left;}
  th{background:#F2EEE6;}
  .num{text-align:right; font-variant-numeric:tabular-nums;}
  .nota{font-size:0.85rem; color:#666; margin-top:20px; border-top:1px dashed var(--bordo); padding-top:12px;}
  .sezione-titolo{font-weight:bold; background:#F2EEE6;}
</style>
</head>
<body>

<h1>📚 Studio di Fattibilità — Libreria Carta da Forno</h1>
<p>Inserisci i dati che avete raccolto. Alla fine premi <strong>Calcola</strong>: la pagina ricarica sé stessa e mostra i risultati in cima.</p>

<?php if ($calcolato): ?>
<div class="risultati <?= $risultati['piano_regge'] ? '' : 'negativo' ?>">
  <h2 style="background:none;color:inherit;padding:0;margin-top:0;">Risultato</h2>

  <div class="verdetto <?= $risultati['piano_regge'] ? 'ok' : 'no' ?>">
    <?php if ($risultati['piano_regge']): ?>
      ✅ Il piano regge: avanzo stimato di <?= euro2($risultati['differenza']) ?> al mese
    <?php else: ?>
      ⚠️ Manca <?= euro2($risultati['gap_mensile']) ?> al mese per raggiungere l'obiettivo
    <?php endif; ?>
  </div>

  <table>
    <tr><th>Voce</th><th class="num">Mensile</th></tr>
    <tr><td>Ricavo libri (da traffico stimato)</td><td class="num"><?= euro2($risultati['ricavo_libri_mese']) ?></td></tr>
    <tr><td>Ricavo eventi (netto)</td><td class="num"><?= euro2($risultati['ricavo_eventi_mese']) ?></td></tr>
    <tr><td>Ricavo prodotti alimentari</td><td class="num"><?= euro2($risultati['ricavo_prodotti_mese']) ?></td></tr>
    <tr><td>Ricavo abbonamenti book club</td><td class="num"><?= euro2($risultati['ricavo_abbonamenti_mese']) ?></td></tr>
    <tr><td>Ricavo workshop</td><td class="num"><?= euro2($risultati['ricavo_workshop_mese']) ?></td></tr>
    <tr><td>Ricavo oggettistica</td><td class="num"><?= euro2($risultati['ricavo_oggetti_mese']) ?></td></tr>
    <tr><td>Ricavo servizi professionali esterni</td><td class="num"><?= euro2($risultati['ricavo_servizi_mese']) ?></td></tr>
    <tr><td>Entrate fisse già esistenti</td><td class="num"><?= euro2($risultati['entrate_fisse_esistenti']) ?></td></tr>
    <tr class="sezione-titolo"><td>TOTALE RICAVI STIMATI</td><td class="num"><?= euro2($risultati['ricavo_totale_stimato']) ?></td></tr>
    <tr><td>Costi fissi mensili (scenario scelto)</td><td class="num"><?= euro2($risultati['cf_scelto']) ?></td></tr>
    <tr><td>Stipendio netto target</td><td class="num"><?= euro2($risultati['stipendio_target']) ?></td></tr>
    <tr class="sezione-titolo"><td>FABBISOGNO MENSILE TOTALE</td><td class="num"><?= euro2($risultati['fabbisogno_mensile']) ?></td></tr>
  </table>

  <?php if (!$risultati['piano_regge']): ?>
  <p><strong>Per colmare il gap, una delle tre leve (o una combinazione) dovrebbe bastare da sola:</strong></p>
  <table>
    <tr><th>Leva</th><th class="num">Quanto in più serve</th></tr>
    <tr><td>Libri venduti in più al giorno</td><td class="num">
      <?= $risultati['libri_giorno_extra'] !== null ? number_format($risultati['libri_giorno_extra'],1,',','.') . ' libri/giorno in più' : 'n/d (dati insufficienti)' ?>
    </td></tr>
    <tr><td>Eventi in più al mese</td><td class="num">
      <?= $risultati['eventi_extra_mese'] !== null ? number_format($risultati['eventi_extra_mese'],1,',','.') . ' eventi/mese in più' : 'n/d (dati insufficienti)' ?>
    </td></tr>
    <tr><td>Ore in più di servizi esterni a settimana</td><td class="num">
      <?= $risultati['ore_extra_servizi_sett'] !== null ? number_format($risultati['ore_extra_servizi_sett'],1,',','.') . ' ore/sett in più' : 'n/d (dati insufficienti)' ?>
    </td></tr>
  </table>
  <?php endif; ?>

  <h2 style="background:none;color:inherit;padding:0;">Tempo</h2>
  <table>
    <tr><td>Ore/settimana richieste in totale</td><td class="num"><?= number_format($risultati['ore_totali_sett'],1,',','.') ?> h</td></tr>
    <tr><td>Ore/settimana a carico di Federica</td><td class="num"><?= number_format($risultati['ore_federica_sett'],1,',','.') ?> h</td></tr>
    <tr><td>Soglia sostenibile impostata</td><td class="num"><?= number_format($risultati['soglia_ore'],1,',','.') ?> h</td></tr>
  </table>
  <p>
    <?php if ($risultati['tempo_sostenibile']): ?>
      ✅ Il carico orario è sostenibile per una persona sola con l'eventuale part-time indicato.
    <?php else: ?>
      ⚠️ Il carico orario supera la soglia sostenibile. Servirebbe un part-time di almeno
      <strong><?= number_format($risultati['ore_parttime_necessarie'],1,',','.') ?> ore/settimana</strong>
      (attualmente impostato a <?= number_format($risultati['ore_parttime'],1,',','.') ?>).
    <?php endif; ?>
  </p>

  <h2 style="background:none;color:inherit;padding:0;">Capitale iniziale</h2>
  <table>
    <tr><td>Totale spese di avviamento (con imprevisti)</td><td class="num"><?= euro($risultati['totale_avviamento']) ?></td></tr>
    <tr><td>Spese ammissibili dal bando (stima)</td><td class="num"><?= euro($risultati['spese_ammissibili_bando']) ?></td></tr>
    <tr><td>Contributo bando atteso (tetto 24.000 €)</td><td class="num"><?= euro($risultati['contributo_bando_atteso']) ?></td></tr>
    <tr><td>Anticipo ottenibile (se fideiussione attiva)</td><td class="num"><?= euro($risultati['anticipo_ottenibile']) ?></td></tr>
    <tr class="sezione-titolo"><td>Capitale che serve avere SUBITO</td><td class="num"><?= euro($risultati['capitale_necessario_subito']) ?></td></tr>
    <tr><td>Liquidità propria disponibile</td><td class="num"><?= euro($risultati['liquidita_disponibile']) ?></td></tr>
    <tr class="sezione-titolo"><td><?= $risultati['gap_liquidita'] > 0 ? 'MANCANO (da prestito/mutuo)' : 'AVANZO rispetto al necessario' ?></td>
        <td class="num"><?= euro(abs($risultati['gap_liquidita'])) ?></td></tr>
  </table>
  <p class="nota">Nota: il bando MiC rimborsa a saldo (in gran parte), non anticipa. Il "capitale necessario subito" è quindi la cifra reale da reperire prima di aprire, anche come prestito/microcredito, indipendentemente dal contributo che arriverà dopo.</p>
</div>
<?php endif; ?>

<form method="post">

<h2>⏱️ Step 1 — Tempo</h2>
<fieldset>
  <div class="campo"><label>Giorni di apertura al pubblico a settimana</label><input type="number" step="0.5" name="giorni_apertura" value="<?= val('giorni_apertura', 6) ?>"></div>
  <div class="campo"><label>Ore di apertura al giorno</label><input type="number" step="0.5" name="ore_apertura_giorno" value="<?= val('ore_apertura_giorno', 8) ?>"></div>
  <div class="campo"><label>Ore/settimana per "dietro le quinte" (ordini, resi, social, magazzino)</label><input type="number" step="0.5" name="ore_retrobottega" value="<?= val('ore_retrobottega', 6) ?>"></div>
  <div class="campo"><label>Eventi al mese (libreria + Forno Demetra)</label><input type="number" step="0.5" name="eventi_mese" value="<?= val('eventi_mese', 2) ?>"></div>
  <div class="campo"><label>Ore per evento (organizzazione + svolgimento)</label><input type="number" step="0.5" name="ore_per_evento" value="<?= val('ore_per_evento', 6) ?>"></div>
  <div class="campo"><label>Ore/settimana per servizi esterni (storytelling, eventi per altri)</label><input type="number" step="0.5" name="ore_servizi_esterni" value="<?= val('ore_servizi_esterni', 10) ?>"></div>
  <div class="campo">
    <label>Assumere una persona part-time?</label>
    <select name="assumere"><option value="no" <?= sel('assumere','no') ?>>No</option><option value="si" <?= sel('assumere','si') ?>>Sì</option></select>
  </div>
  <div class="campo"><label>Se sì: ore/settimana coperte dal part-time</label><input type="number" step="0.5" name="ore_parttime" value="<?= val('ore_parttime', 0) ?>"></div>
  <div class="campo"><label>Soglia di ore/settimana considerata sostenibile per una persona sola <span class="nuovo">★ aggiunto</span></label><input type="number" step="0.5" name="soglia_ore" value="<?= val('soglia_ore', 45) ?>"></div>
</fieldset>

<h2>💶 Step 2 — Costi di avviamento</h2>
<fieldset>
  <div class="campo"><label>Mq del locale</label><input type="number" name="mq_locale" value="<?= val('mq_locale', 22) ?>"></div>
  <div class="campo"><label>Canone di affitto mensile (€)</label><input type="number" name="canone_mensile" value="<?= val('canone_mensile', 400) ?>"></div>
  <div class="campo"><label>Deposito cauzionale (mesi di affitto)</label><input type="number" name="deposito_mesi" value="<?= val('deposito_mesi', 3) ?>"></div>
  <div class="campo"><label>Costo lavori/allestimento (€)</label><input type="number" name="costo_lavori" value="<?= val('costo_lavori', 6000) ?>"></div>
  <div class="campo"><label>Costo insegna e immagine coordinata (€)</label><input type="number" name="costo_insegna" value="<?= val('costo_insegna', 1500) ?>"></div>
  <div class="campo"><label>Costo setup software gestionale (€)</label><input type="number" name="costo_software_setup" value="<?= val('costo_software_setup', 500) ?>"></div>
  <div class="campo"><label>Costo primo assortimento libri (€)</label><input type="number" name="costo_stock_iniziale" value="<?= val('costo_stock_iniziale', 8000) ?>"></div>
  <div class="campo"><label>% dello stock in conto deposito (non acquistato subito)</label><input type="number" name="perc_conto_deposito" value="<?= val('perc_conto_deposito', 30) ?>"></div>
  <div class="campo"><label>Spese notarili/commercialista per l'apertura (€)</label><input type="number" name="costo_notaio_comm" value="<?= val('costo_notaio_comm', 800) ?>"></div>
  <div class="campo"><label>Costo polizza fideiussoria per anticipo bando (€, 0 se non richiesta)</label><input type="number" name="costo_polizza" value="<?= val('costo_polizza', 0) ?>"></div>
  <div class="campo"><label>% delle spese di avviamento ammissibili dal bando</label><input type="number" name="perc_ammissibile_bando" value="<?= val('perc_ammissibile_bando', 80) ?>"></div>
  <div class="campo"><label>% imprevisti da aggiungere</label><input type="number" name="perc_imprevisti" value="<?= val('perc_imprevisti', 10) ?>"></div>
  <div class="campo"><label>Liquidità propria disponibile oggi (€) <span class="nuovo">★ aggiunto</span></label><input type="number" name="liquidita_disponibile" value="<?= val('liquidita_disponibile', 6000) ?>"></div>
</fieldset>

<h2>📆 Step 2 — Costi fissi mensili</h2>
<fieldset>
  <div class="campo"><label>Utenze mensili (€)</label><input type="number" name="utenze_mensili" value="<?= val('utenze_mensili', 150) ?>"></div>
  <div class="campo"><label>Internet/telefono mensile (€)</label><input type="number" name="telefono_mensile" value="<?= val('telefono_mensile', 40) ?>"></div>
  <div class="campo"><label>Assicurazione mensile (se annua, dividi per 12) (€)</label><input type="number" name="assicurazione_mensile" value="<?= val('assicurazione_mensile', 50) ?>"></div>
  <div class="campo"><label>Software gestionale in abbonamento, mensile (€)</label><input type="number" name="software_abbon" value="<?= val('software_abbon', 30) ?>"></div>
  <div class="campo"><label>Commercialista, mensile o forfait/12 (€)</label><input type="number" name="commercialista_mens" value="<?= val('commercialista_mens', 100) ?>"></div>
  <div class="campo"><label>Contributi INPS mensili stimati (€)</label><input type="number" name="contributi_inps" value="<?= val('contributi_inps', 300) ?>"></div>
  <div class="campo"><label>Costo mensile part-time (stipendio + contributi), se attivato (€)</label><input type="number" name="costo_parttime_mens" value="<?= val('costo_parttime_mens', 0) ?>"></div>
  <div class="campo">
    <label>Regime fiscale forfettario?</label>
    <select name="regime_forfettario"><option value="si" <?= sel('regime_forfettario','si') ?>>Sì</option><option value="no" <?= sel('regime_forfettario','no') ?>>No</option></select>
  </div>
</fieldset>

<h2>📈 Step 3 — Ricavi</h2>
<fieldset>
  <div class="campo"><label>Prezzo medio di copertina di un libro (€)</label><input type="number" step="0.5" name="prezzo_medio_libro" value="<?= val('prezzo_medio_libro', 18) ?>"></div>
  <div class="campo"><label>Margine % trattenuto sul prezzo di copertina</label><input type="number" name="margine_libro_perc" value="<?= val('margine_libro_perc', 32) ?>"></div>
  <div class="campo"><label>Persone che entrano in negozio in un giorno feriale medio</label><input type="number" name="persone_giorno" value="<?= val('persone_giorno', 15) ?>"></div>
  <div class="campo"><label>% di chi entra che compra qualcosa</label><input type="number" name="perc_acquisto" value="<?= val('perc_acquisto', 30) ?>"></div>

  <div class="campo"><label>Prezzo medio biglietto/evento a pagamento (€)</label><input type="number" step="0.5" name="prezzo_evento" value="<?= val('prezzo_evento', 12) ?>"></div>
  <div class="campo"><label>Capienza media di un evento (persone)</label><input type="number" name="capienza_evento" value="<?= val('capienza_evento', 20) ?>"></div>
  <div class="campo"><label>% incasso che resta a voi se l'evento è al Forno Demetra (100 se non condiviso)</label><input type="number" name="perc_trattenuta_demetra" value="<?= val('perc_trattenuta_demetra', 100) ?>"></div>
  <div class="campo"><label>Costo materie prime per evento/degustazione (€)</label><input type="number" name="materie_prime_evento" value="<?= val('materie_prime_evento', 50) ?>"></div>

  <div class="campo"><label>Vendere prodotti alimentari da subito?</label>
    <select name="vendi_prodotti"><option value="si" <?= sel('vendi_prodotti','si') ?>>Sì</option><option value="no" <?= sel('vendi_prodotti','no') ?>>No</option></select>
  </div>
  <div class="campo"><label>Quanti prodotti alimentari venduti al giorno <span class="nuovo">★ aggiunto</span></label><input type="number" step="0.5" name="prodotti_al_giorno" value="<?= val('prodotti_al_giorno', 3) ?>"></div>
  <div class="campo"><label>Scontrino medio prodotto alimentare (€)</label><input type="number" step="0.5" name="scontrino_prodotto" value="<?= val('scontrino_prodotto', 10) ?>"></div>
  <div class="campo"><label>Margine % medio sui prodotti alimentari</label><input type="number" name="margine_prodotto_perc" value="<?= val('margine_prodotto_perc', 45) ?>"></div>

  <div class="campo"><label>Attivare l'abbonamento book club da subito?</label>
    <select name="attiva_abbonamento"><option value="si" <?= sel('attiva_abbonamento','si') ?>>Sì</option><option value="no" <?= sel('attiva_abbonamento','no') ?>>No</option></select>
  </div>
  <div class="campo"><label>Periodicità abbonamento</label>
    <select name="periodicita_abbon"><option value="mensile" <?= sel('periodicita_abbon','mensile') ?>>Mensile</option><option value="annuale" <?= sel('periodicita_abbon','annuale') ?>>Annuale</option></select>
  </div>
  <div class="campo"><label>Prezzo abbonamento (€, secondo periodicità)</label><input type="number" step="0.5" name="prezzo_abbonamento" value="<?= val('prezzo_abbonamento', 15) ?>"></div>
  <div class="campo"><label>Numero abbonati stimato nei primi mesi <span class="nuovo">★ aggiunto</span></label><input type="number" name="numero_abbonati" value="<?= val('numero_abbonati', 10) ?>"></div>

  <div class="campo"><label>Includere i workshop di scrittura come flusso separato?</label>
    <select name="attiva_workshop"><option value="si" <?= sel('attiva_workshop','si') ?>>Sì</option><option value="no" <?= sel('attiva_workshop','no') ?>>No</option></select>
  </div>
  <div class="campo"><label>Prezzo a partecipante workshop (€)</label><input type="number" step="0.5" name="prezzo_workshop" value="<?= val('prezzo_workshop', 25) ?>"></div>
  <div class="campo"><label>Capienza media workshop (persone)</label><input type="number" name="capienza_workshop" value="<?= val('capienza_workshop', 10) ?>"></div>
  <div class="campo"><label>Workshop al mese <span class="nuovo">★ aggiunto</span></label><input type="number" step="0.5" name="workshop_al_mese" value="<?= val('workshop_al_mese', 1) ?>"></div>

  <div class="campo"><label>Includere un angolo oggettistica/cartoleria a tema?</label>
    <select name="attiva_oggettistica"><option value="si" <?= sel('attiva_oggettistica','si') ?>>Sì</option><option value="no" <?= sel('attiva_oggettistica','no') ?>>No</option></select>
  </div>
  <div class="campo"><label>Scontrino medio oggettistica (€)</label><input type="number" step="0.5" name="scontrino_oggetti" value="<?= val('scontrino_oggetti', 15) ?>"></div>
  <div class="campo"><label>Margine % oggettistica</label><input type="number" name="margine_oggetti_perc" value="<?= val('margine_oggetti_perc', 50) ?>"></div>
  <div class="campo"><label>Pezzi di oggettistica venduti al mese <span class="nuovo">★ aggiunto</span></label><input type="number" name="oggetti_al_mese" value="<?= val('oggetti_al_mese', 5) ?>"></div>

  <div class="campo"><label>Tariffa oraria media servizi professionali esterni (€)</label><input type="number" step="0.5" name="tariffa_servizi_esterni" value="<?= val('tariffa_servizi_esterni', 25) ?>"></div>
  <div class="campo"><label>Entrate fisse già esistenti al mese (es. Sabri) (€)</label><input type="number" name="entrate_fisse_esistenti" value="<?= val('entrate_fisse_esistenti', 500) ?>"></div>
</fieldset>

<h2>🎯 Step 4 — Obiettivo</h2>
<fieldset>
  <div class="campo"><label>Stipendio netto mensile minimo da raggiungere entro 12-18 mesi (€)</label><input type="number" name="stipendio_target" value="<?= val('stipendio_target', 1200) ?>"></div>
</fieldset>

<button class="btn" type="submit" name="calcola" value="1">Calcola →</button>
</form>

<p class="nota">
Strumento di stima approssimata a uso interno, non sostituisce il business plan formale né una consulenza di un commercialista.<br>
Campi con <span class="nuovo">★ aggiunto</span>: non erano nell'elenco originale di domande — servono per chiudere i calcoli su volumi che altrimenti restavano indefiniti (es. non bastava sapere il margine su un prodotto, serviva anche quanti se ne vendono).
</p>

</body>
</html>
