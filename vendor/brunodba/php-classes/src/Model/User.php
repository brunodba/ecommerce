<?php

namespace Hcode\Model;

use Exception;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {
    // Criamos uma constante para futuramente possibilitar a reutilização.
    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";

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

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            "desperson"=>$this->getdesperson(),
            "deslogin"=>$this->getdeslogin(),
            "despassword"=>$this->getdespassword(),
            "desemail"=>$this->getdesemail(),
            "nrphone"=>$this->getnrphone(),
            "inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    public function get($iduser)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    public function update()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            "iduser"=>$this->getiduser(),
            "desperson"=>$this->getdesperson(),
            "deslogin"=>$this->getdeslogin(),
            "despassword"=>$this->getdespassword(),
            "desemail"=>$this->getdesemail(),
            "nrphone"=>$this->getnrphone(),
            "inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(
            ":email"=>$email
        ));
        if (count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {
            $data = $results[0];
            $recoveries = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));
            if (count($recoveries) === 0)
            {
                throw new \Exception("Não foi possível recuperar a senha");
            }
            else
            {
                $dataRecovery = $recoveries[0];
                $cipher="AES-128-ECB";
                $ivlen = openssl_cipher_iv_length($cipher);
                $iv = openssl_random_pseudo_bytes($ivlen);
                $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"],$cipher,User::SECRET,$options = 0, $iv));
                $link = "http://www.bcoecommerce.com.br/admin/forgot/reset?code=$code";
            }
        }
    }
}