<?php
    class Posto {
        public bool $occupato;
        public string $targa;
        public DateTime $oraIngresso;

        public function __construct() {
            $this->occupato = false;
            $this->targa = "";
            $this->oraIngresso = new DateTime();
        }

        public function liberaPosto() {
            $this->occupato = false;
            $this->targa = "";
        }

        public function occupaPosto(string $targa, DateTime $oraIngresso) {
            $this->occupato = true;
            $this->targa = $targa;
            $this->oraIngresso = $oraIngresso;
        }
    }

?>