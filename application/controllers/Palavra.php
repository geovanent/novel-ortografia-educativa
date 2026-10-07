<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Palavra extends Jogo_Controller {
    protected $mode = 'palavra';
    public function jogarPalavra() { $this->start(); }
    public function inserirRodadaPalavra() { $this->finish(); }
}
