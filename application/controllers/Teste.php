<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Teste extends Jogo_Controller {
    protected $mode = 'teste';
    public function jogarTeste() { $this->start(); }
    public function inserirRodadaTeste() { $this->finish(); }
}
