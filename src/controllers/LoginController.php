<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;

class LoginController extends Controller
{

    public function signin()
    {
        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash']= '';
        }
        $this->render('signin',[ 'flash' => $flash ]);
    }

    public function signinAction()
    {
        $email = strtolower(trim(filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL)));
        $password = filter_input(INPUT_POST,'password');

        if ($email && $password) {
            $token = UserHandler::verfifyLogin($email, $password);

            if (!empty($token)) {
                $_SESSION['token'] = $token;
                $this->redirect('/');
            } else {
                $_SESSION['flash'] = 'E-mail e/ou senha não conferem.';
                $this->redirect('/signin');
            }
        } else {
            $this->redirect('/signin');
        }
    }

    public function signup()
    {
        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash']= '';
        }
        $this->render('signup',[ 'flash' => $flash ]);
    }

    public function signupAction()
    {
        $name = ucwords(strtolower(trim(filter_input(INPUT_POST,'name', FILTER_SANITIZE_SPECIAL_CHARS))));
        $email = strtolower(trim(filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL)));
        $password = filter_input(INPUT_POST,'password');
        $birthdate = filter_input(INPUT_POST,'birthdate');
  
        if ($name && $email && $password && $birthdate) {
            // $birthdate = explode('/', $birthdate);

            // if (count($birthdate) != 3) {
            //     $_SESSION['flash'] = 'Data de nascimento inválida!';
            //     $this->redirect('/signup');
            // }
            // $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
            if (strtotime($birthdate) === false) {
                $_SESSION['flash'] = 'Data de nascimento inválida!';
                $this->redirect('/signup');
            }

            if (UserHandler::emailExists($email) === false) {
                $token = UserHandler::addUser($name, $email, $password, $birthdate);                
                $_SESSION['token'] = $token;
                $this->redirect('/');
            } else {
                $_SESSION['flash'] = 'E-mail já cadastrado!';
                $this->redirect('/signup');
            }
        } else {
            $this->redirect('/signup');
        }
    }
}