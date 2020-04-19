<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\LoginHandler;

class HomeController extends Controller {

    private $loggedUser;
    
    public function __construct()
    {
        $this->loggedUser = LoginHandler::checkLogin();
        if (empty($this->loggedUser)) {            
            $this->redirect('/signin');
        }
    }

    public function index() 
    {
        $this->render('home', ['nome' => 'Bonieky']);
    }
}