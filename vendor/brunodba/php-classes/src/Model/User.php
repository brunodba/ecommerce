<?php

namespace Hcode\Model;

use Exception;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {
    // Criamos uma constante para futuramente possibilitar a reutilização.
    const SESSION = "User";
    public static function login($login, $password){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));
        if(count($results)===0)
        {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
        $data = $results[0];
        if (password_verify($password, $data["despassword"]) === true)
        {
            $user = new User();
            # $user->setiduser($data["iduser"]); Para dar o set somente no ID usuário, criamos o método para passar de forma dinâmica os campos e 
            # tiramos a definição de qual indece, ficando o array por inteiro.
            $user->setData($data);
            // Aqui precisamos criar uma sessão para identificar se o usuário está autenticado ou não.
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;
        } else {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if (
            !isset($_SESSION[User::SESSION]) // Existe sessão
            ||
            !$_SESSION[User::SESSION] // Não está vazia
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 // o ID maior que zero é um possivel usuário
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin // Na sessão está tendando acessar a administração.
        ){
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }
}