<?php ?>
<html>
    <div style="border: 2px solid black; border-radius: 5px; padding: 10px; width: 200px; height: 200px; margin: 20px; display:grid; justify-items: center; align-items: center;">
        <div>
            <label>Targa:</label>
            <label><?php echo $_SESSION["posti"][$i]->targa; ?></label>
        </div>
        
        <div>
            <label>Ora Ingresso:</label>
            <label><?php echo $_SESSION["posti"][$i]->oraIngresso->format('H:i:s'); ?></label>
        </div>
    </div>
</html>
<?php ?>