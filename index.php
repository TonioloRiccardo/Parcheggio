<?php 
    require_once 'posto.php';
    session_start();

    $numeroPosti = 10;
    $nomeFile = "posti.json";

    // if (!isset($posti)) {
    //     /** @var Posto[] $posti */
    //     $posti = [];

    //     for ($i=0; $i < $numeroPosti; $i++) { 
    //         $posti[$i] = new Posto();
    //     }
    // }
    
    if (!isset($_SESSION["numeroPostiLiberi"])) {
        $_SESSION["numeroPostiLiberi"] = 10;
    }

    /** @var Posto[] $posti */
    $posti = [];

    // Controlla se il file esiste già
    if (file_exists($nomeFile)) {
        // 1. Legge il testo dal file
        $testoJson = file_get_contents($nomeFile);
        
        // 2. Converte il JSON in un array PHP (il 'true' serve per avere array associativi)
        $datiArray = json_decode($testoJson, true);

        // 3. Ricostruisce gli oggetti Posto
        for ($i = 0; $i < $numeroPosti; $i++) {
            $posti[$i] = new Posto();
            
            // Se nel JSON questo posto risulta occupato, lo riempiamo
            if (isset($datiArray[$i]) && $datiArray[$i]["occupato"] === true) {
                $posti[$i]->occupaPosto(
                    $datiArray[$i]["targa"],
                    new DateTime($datiArray[$i]["oraIngresso"]) // Riconverte la stringa in DateTime
                );
            }
        }
    } else {
        // Se il file non esiste ancora, crea 10 posti vuoti
        for ($i = 0; $i < $numeroPosti; $i++) {
            $posti[$i] = new Posto();
        }
    }


    // if (!isset($_SESSION["posti"])) {
    //     $_SESSION["posti"] = [];

    //     for ($i=0; $i < $numeroPosti; $i++) { 
    //         $_SESSION["posti"][$i] = new Posto();
    //     }
    // } 

    // if($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $postoLibero = false;
    //     for ($i=0; $i < sizeof($_SESSION["posti"]); $i++) { 
    //         if($_SESSION["posti"][$i]->occupato == false) {
    //             $postoLibero = true;

    //             $_SESSION["posti"][$i]->occupaPosto($_POST["targa"], new DateTime());
    //             break;
    //         }
    //     }
    // }

    // if($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $posti = $_POST["posti"];

    //     $postoLibero = false;
    //     for ($i=0; $i < $numeroPosti; $i++) { 
    //         $postoLibero = true;
    //         $posti[$i]->occupaPosto($_POST["targa"], new DateTime());
    //         break;
    //     }
    // }
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