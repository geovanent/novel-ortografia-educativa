<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Texto extends Jogo_Controller {
    protected $mode = 'texto';
    public function jogarTexto() { $this->start(); }
    public function inserirRodadaTexto() { $this->finish(); }
}
