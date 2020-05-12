<?php

namespace Hcode;

class PageAdmin extends Page {

    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    { // como essa classe é uma herança da classe Page nós apenas reutilizamos o método construtor já existente nela
        parent::__construct($opts, $tpl_dir);
    }
}