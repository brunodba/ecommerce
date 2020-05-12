<?php

namespace Hcode;

class Model {

    private $values = [];
    public function __call($name, $args)
    {
        $method = substr($name, 0, 3); // posicao 0 traz 3 caracteres.
        $fieldName = substr($name, 3, strlen($name)); // a partir da posicao 3 até o fim.

        switch ($method) {
            case 'get':
                    return $this->values[$fieldName];
                break;
                
            case 'set':
                    $this->values[$fieldName] = $args[0];
                break;
        }
    }
    # colocamos nesta classe para disponibilizar para todas as models.
    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            // colocamos entre aspas para que o PHP recolheça que será dinâmico o método a ser chamado. $key conte o nome de dada coluna.
            $this->{"set".$key}($value);
        }
    }
    # Método para pegar os valores, values é uma variavel definida como privada, por isso o $this.
    public function getValues()
    {
        return $this->values;
    }
}