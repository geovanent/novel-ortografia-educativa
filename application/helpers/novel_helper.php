<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Encode each path segment, including cedillas, spaces and ampersands. */
function novel_asset_url($path)
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    return base_url(implode('/', array_map('rawurlencode', explode('/', $path))));
}

function novel_image_url($path)
{
    $path = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
    if (strpos($path, 'assets/img/') !== 0) {
        $path = 'assets/img/'.$path;
    }
    if (strpos($path, '..') !== FALSE || !preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $path) || !is_file(FCPATH.$path)) {
        return novel_asset_url('assets/img/illustration-unavailable.svg');
    }
    return novel_asset_url($path);
}

function novel_avatar($avatar)
{
    return in_array($avatar, array('cecilia', 'clarice', 'graciliano', 'verissimo'), TRUE) ? $avatar : 'graciliano';
}

function novel_notify($message, $type = 'error')
{
    return '<script>Novel.notify('.json_encode((string) $message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).', '.json_encode($type).');</script>';
}

function novel_duration($seconds) {
    $seconds=max(0,(int)$seconds);
    return sprintf('%02d:%02d:%02d',intdiv($seconds,3600),intdiv($seconds%3600,60),$seconds%60);
}

function novel_transcript($path) {
    static $text;
    if ($text===NULL) $text=json_decode(file_get_contents(APPPATH.'content/transcripts.json'),TRUE);
    return isset($text[$path])?$text[$path]:'';
}
function novel_scene_title($name) {
    $titles=array('cena1'=>'Chegada à Academia','cena2'=>'Primeiro avanço','cena3'=>'Foco e preparação','cena4'=>'Golpes verbais','cena5'=>'Exceções e defesa','cena6'=>'A fonte de sabedoria','cena7'=>'Ataque à biblioteca','cena8'=>'As Gramáticas Notáveis','cena9'=>'O confronto dos mestres','cena10'=>'Retomar a Academia','cena11'=>'Entrada na Academia','cena12'=>'Os corredores','cena13'=>'Os guardas do santuário','cena14'=>'O conhecimento do Léxicon','cena15'=>'A Torre Fonética','cena16'=>'Resgate do mestre','cena17'=>'A batalha final');
    return isset($titles[$name])?$titles[$name]:'História da Academia';
}
