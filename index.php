<!DOCTYPE html>
<head>

</head>
<body>
    <?php 
        require_once 'posto.php';
        session_start();

        $numeroPosti = 10;
        $_SESSION["posti"] = [];

        for ($i=0; $i < $numeroPosti; $i++) { 
            $_SESSION["posti"][$i] = new Posto();
        }
    ?>

    <div>
        <?php for ($i=0; $i < $numeroPosti; $i++): ?>
            <div>
                <label for="targa">Targa:</label>
                <p><?php echo $_SESSION["posti"][$i]->targa; ?></p>

                <label for="ora_ingresso">Ora Ingresso:</label>
                <p><?php echo $_SESSION["posti"][$i]->oraIngresso; ?></p>
            </div>
        <?php endfor; ?>
    </div>
</body>