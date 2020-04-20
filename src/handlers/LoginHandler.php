<?php
namespace src\handlers;

use \src\models\User;

class LoginHandler {
    
    public static function checkLogin(): ?User
    {
        if (!empty($_SESSION['token'])) {
            $token = $_SESSION['token'];

            $data = User::select()->where('token', $token)->one();
               
            if (count(array($data)) > 0) {
                $loggedUser = new User();
                $loggedUser->id = $data['id'];
                $loggedUser->name = $data['name'];
                $loggedUser->avatar = $data['avatar'];

                return $loggedUser;
            }
        }
        return null;
    }

    public static function verfifyLogin(string $email, string $password): ?string
    {
        $user = User::select()->where('email', $email)->one();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $token = LoginHandler::tokenGenerator(); 
                User::update()
                    ->set('token', $token)
                    ->where('email', $email)
                ->execute();
                
                return $token;
            }
        }        
        return null;
    }

    public static function emailExists($email): bool
    {
        $user = User::select()->where('email', $email)->one();
        return $user? true : false;
    }

    public static function addUser($name, $email, $password, $birthdate): string
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = LoginHandler::tokenGenerator();      
            
        User::insert([
            'email' => $email,
            'password' => $hash,
            'name' => $name,
            'birthdate' => $birthdate,
            // 'avatar' => 'default.jpg', //default no banco
            // 'cover' => 'cover.jpg',
            'token' => $token
        ])->execute();
        
        return $token;
    }

    public static function tokenGenerator()
    {
        return md5(time().rand(0,9999)).time();
    }
}