<?php 
    require_once 'posto.php';
    session_start();

    $numeroPosti = 10;
    $percorsoFile = __DIR__ . '/dati.json';

    if (!isset($_SESSION["numeroPostiLiberi"])) {
        $_SESSION["numeroPostiLiberi"] = 10;
    }

    /** @var Posto[] $posti */
    $posti = [];

    // 1. APERTURA E LETTURA ALL'AVVIO
    if (file_exists($percorsoFile)) {
        $handle = fopen($percorsoFile, 'r');
        if ($handle === false) {
            die('Impossibile aprire il file JSON in lettura.');
        }

        flock($handle, LOCK_SH);
        $dimensione = filesize($percorsoFile);
        $testoJson  = $dimensione > 0 ? fread($handle, $dimensione) : '';
        flock($handle, LOCK_UN);
        fclose($handle);

        $datiArray = json_decode($testoJson ?: '[]', true);
        if (!is_array($datiArray)) {
            $datiArray = [];
        }

        for ($i = 0; $i < $numeroPosti; $i++) {
            $posti[$i] = new Posto();

            if (isset($datiArray[$i]) && !empty($datiArray[$i]['occupato'])) {
                $ora = !empty($datiArray[$i]['oraIngresso'])
                    ? new DateTime($datiArray[$i]['oraIngresso'])
                    : new DateTime();

                $posti[$i]->occupaPosto((string) $datiArray[$i]['targa'], $ora);
            }
        }
    } else {
        // Prima esecuzione: crea 10 posti vuoti
        for ($i = 0; $i < $numeroPosti; $i++) {
            $posti[$i] = new Posto();
        }
    }

    // 2. GESTIONE ARRIVO / PARTENZA QUANDO VIENE INVIATA UNA TARGA
    $messaggio = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['targa'])) {
        $targa = strtoupper(trim($_POST['targa']));

        if ($targa !== '') {
            $indiceTrovato = null;
            for ($i = 0; $i < $numeroPosti; $i++) {
                if ($posti[$i]->occupato && strtoupper($posti[$i]->targa) === $targa) {
                    $indiceTrovato = $i;
                    break;
                }
            }

            if ($indiceTrovato !== null) {
                // La macchina era già presente: esce e libera il posto
                $posti[$indiceTrovato]->liberaPosto();
                $messaggio = "Auto con targa {$targa} uscita: posto #" . ($indiceTrovato + 1) . " liberato.";
            } else {
                // La macchina entra: cerca il primo posto libero
                $postoLibero = null;
                for ($i = 0; $i < $numeroPosti; $i++) {
                    if (!$posti[$i]->occupato) {
                        $postoLibero = $i;
                        break;
                    }
                }

                if ($postoLibero !== null) {
                    $posti[$postoLibero]->occupaPosto($targa, new DateTime());
                    $messaggio = "Auto con targa {$targa} entrata nel posto #" . ($postoLibero + 1) . ".";
                } else {
                    $messaggio = "Parcheggio completo: nessun posto libero.";
                }
            }

            // 3. SALVATAGGIO AGGIORNATO SU FILE JSON
            $daSalvare = [];
            for ($i = 0; $i < $numeroPosti; $i++) {
                $daSalvare[] = [
                    'posto'       => $i + 1,
                    'occupato'    => $posti[$i]->occupato,
                    'targa'       => $posti[$i]->targa,
                    'oraIngresso' => $posti[$i]->occupato
                        ? $posti[$i]->oraIngresso->format(DateTime::ATOM)
                        : '',
                ];
            }

            $json = json_encode($daSalvare, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                die('Errore nella codifica JSON: ' . json_last_error_msg());
            }

            $handleWrite = fopen($percorsoFile, 'w');
            if ($handleWrite === false) {
                die('Impossibile aprire il file JSON in scrittura.');
            }

            flock($handleWrite, LOCK_EX);
            $scritti = fwrite($handleWrite, $json);
            fflush($handleWrite);
            flock($handleWrite, LOCK_UN);
            fclose($handleWrite);

            if ($scritti === false) {
                die('Errore durante il salvataggio del file JSON.');
            }
        }
    }

    // Aggiorna i dati in sessione per coerenza con il resto del progetto
    $postiLiberi = 0;
    foreach ($posti as $p) {
        if (!$p->occupato) {
            $postiLiberi++;
        }
    }
    $_SESSION['posti']              = $posti;
    $_SESSION['numeroPostiLiberi'] = $postiLiberi;
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); justify-items: center; align-items: center;">
            <?php for ($i=0; $i < $numeroPosti; $i++): ?>
                <?php if (!$_SESSION["posti"][$i]->occupato): ?>
                    <div class="posto"></div>
                <?php else: ?>
                    <div class="posto occupato" >
                        <div>
                            <label>Targa:</label>
                            <label><?php echo $_SESSION["posti"][$i]->targa; ?></label>
                        </div>
                        
                        <div>
                            <label>Ora Ingresso:</label>
                            <label><?php echo $_SESSION["posti"][$i]->oraIngresso->format('H:i:s'); ?></label>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <div>
            <form method="post" action="index.php">
                <label for="targa">
                    Targa: 
                    <input type="text" name="targa" maxlength="7" minlength="7">
                </label>
                <input type="submit" value="Invia">
            </form>
        </div>
    </body>
</html>