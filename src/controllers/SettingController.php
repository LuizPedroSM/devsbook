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

        // Avatar
        if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
            
            $newAvatar = $_FILES['avatar'];
            if (in_array($newAvatar['type'],['image/jpeg','image/jpg','image/png'])) {
                $avatarName = $this->cutImage($newAvatar,200,200,'media/avatars');
                $user->avatar = $avatarName;
            }
        }
        // Cover
        if (isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])) {
            $newCover = $_FILES['cover'];
            if (in_array($newCover['type'],['image/jpeg','image/jpg','image/png'])) {
                $coverName = $this->cutImage($newCover,850,310,'media/covers');
                $user->cover = $coverName;
            }
        }
        
        UserHandler::updateUser($user);

        $this->redirect('/settings');
    }

    private function cutImage($file, $width, $height, $folder)
    {
        list($widthOrig, $hieghtOrig) = getimagesize($file['tmp_name']);
        $ratio = $widthOrig/$hieghtOrig;

        $newWidth = $width;
        $newHeight = $newWidth / $ratio;

        if ($newHeight < $height) {
            $newHeight = $height;
            $newWidth = $newHeight * $ratio;
        }

        $x = $width - $newWidth;
        $y = $height - $newHeight;
        $x = $x < 0 ? $x/2 : $x;
        $y = $y < 0 ? $y/2 : $y;

        $finalImage = imagecreatetruecolor($width,$height);
        switch ($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
        }

        imagecopyresampled(
            $finalImage, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight, $widthOrig, $hieghtOrig
        );

        $filename = md5(time().rand(0,9999)).'.jpg';
        imagejpeg($finalImage, $folder.'/'.$filename);

        return $filename;
    }

}