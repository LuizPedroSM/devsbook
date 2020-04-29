<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;

class SettingController extends Controller 
{
    private $loggedUser;
    
    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if (empty($this->loggedUser)) {            
            $this->redirect('/signin');
        }
    }

    public function index($atts = []) 
    {
        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash']= '';
        }
        $user = UserHandler::getUser($this->loggedUser->id);
        $this->render('settings', [
            'loggedUser' => $this->loggedUser,
            'flash' => $flash,
            'user' => $user
        ]);
    }

    public function update()
    {
        $name = ucwords(strtolower(trim(filter_input(INPUT_POST,'name', FILTER_SANITIZE_SPECIAL_CHARS))));
        $email = strtolower(trim(filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL)));
        $birthdate = filter_input(INPUT_POST,'birthdate');
        $city = filter_input(INPUT_POST,'city');
        $work = filter_input(INPUT_POST,'work');
        $newPassword = filter_input(INPUT_POST,'newPassword');
        $confirmPassword = filter_input(INPUT_POST,'confirmPassword');

        $user = UserHandler::getUser($this->loggedUser->id);
        if ($email != $user->email && UserHandler::emailExists($email)) {
            $_SESSION['flash'] = 'E-mail já cadastrado!';
            $this->redirect('/settings');
        }

        if (strtotime($birthdate) === false) {
            $_SESSION['flash'] = 'Data de nascimento inválida!';
            $this->redirect('/settings');
        }
        if ($newPassword != $confirmPassword) {
            $_SESSION['flash'] = 'Senhas não são iguais';
            $this->redirect('/settings');
        }
        $user->email = $email;
        $user->birthdate = $birthdate;
        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->city = $city;
        $user->work = $work;
   
        UserHandler::updateUser($user);
        $this->redirect('/settings');
    }

}