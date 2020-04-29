<?php
namespace src\handlers;

use \src\models\User;
use \src\models\UserRelation;
use \src\handlers\PostHandler;

class UserHandler {
    
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
                $token = self::tokenGenerator(); 
                User::update()
                    ->set('token', $token)
                    ->where('email', $email)
                ->execute();
                
                return $token;
            }
        }        
        return null;
    }

    public static function idExists($id): bool
    {
        $user = User::select()->where('id', $id)->one();
        return $user? true : false;
    }

    public static function emailExists($email): bool
    {
        $user = User::select()->where('email', $email)->one();
        return $user? true : false;
    }

    public static function getUser(int $id, bool $full = false): ?User
    {
        $data = User::select()->where('id', $id)->one();

        if ($data) {
            $user = new User();
            $user->id = $data['id'];
            $user->name = $data['name'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'];
            $user->work = $data['work'];
            $user->avatar = $data['avatar'];
            $user->cover = $data['cover'];

            if ($full) {
                $user->followers = [];
                $user->following = [];
                $user->photos = [];

                //followers
                $followers = UserRelation::select()->where('user_to', $id)->get();
                foreach ($followers as $follower) {
                    $userData = User::select()->where('id', $follower['user_from'])->one();
                    
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->followers[] = $newUser;
                }

                //following
                $following = UserRelation::select()->where('user_from', $id)->get();
                foreach ($following as $follower) {
                    $userData = User::select()->where('id', $follower['user_to'])->one();
                    
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->following[] = $newUser;
                }

                //photos
                $user->photos = PostHandler::getPhotosFrom($id);
            }
            return $user;
        }
        return null;
    }

    public static function addUser($name, $email, $password, $birthdate): string
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = self::tokenGenerator();      
            
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

    public static function isFollowing(int $from, int $to): bool
    {
        $data = UserRelation::select()
            ->where('user_from', $from)
            ->where('user_to', $to)
        ->one();

        return $data? true : false;
    }

    public static function follow(int $from, int $to)
    {
        UserRelation::insert([
            'user_from' => $from,
            'user_to' => $to,
        ])->execute();
    }

    public static function unfollow(int $from, int $to)
    {
        UserRelation::delete()
            ->where('user_from', $from)
            ->where('user_to', $to)
        ->execute();
    }

    public static function searchUser(string $term): ?array
    {
        $data = User::select()->where('name', 'like', '%'.$term.'%')->get();
        if ($data) {
            foreach ($data as $user) {
                $newUser = new User();
                $newUser->id = $user['id'];
                $newUser->name = $user['name'];
                $newUser->avatar = $user['avatar'];
                
                $users[] = $newUser;
            }
        }
        return $users ?? [];
    }
}