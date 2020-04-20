<?php
namespace src\handlers;

use \src\models\Post;
use \src\models\User;
use \src\models\UserRelation;

class PostHandler {
    public static function addPost(int $idUser, string $type, $body)
    {
        if (!empty($idUser && !empty(trim($body)))) {
            Post::insert([
                'id_user' => $idUser,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'body' => $body
            ])->execute();
        }
    }

    public static function getHomeFeed(int $idUser,int $page)
    {
        $perPage = 2;
        // 1. Pegar lista de usuários que EU sigo.
        $userList = UserRelation::select()
            ->where('user_from', $idUser)
        ->get();
        
        $users = [$idUser];
        foreach ($userList as $userItem) {
            $user[] = $userItem['user_to'];
        }
        // 2. Pegar os posts dessa galera ordenado pela data.
        $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', 'in', $users)
        ->count();
        $pageCount = ceil($total/$perPage);


        // 3. Transformar o resultado em objetos dos models
        $posts = [];
        foreach ($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;
            
            // verifica se o post pertence ao usuário logado habilitando novas opções
            if ($postItem['id_user'] == $idUser) {
                $newPost->mine = true;
            }

            // 4. preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];
            
            // TODO: 4.1 preencher as informações de LIKE
            $newPost->likeCount = 0;
            $newPost->liked = false;
            // TODO: 4.2 preencher as informações de COMMENTS
            $newPost->comments = [];

            $posts[] = $newPost;
        }
        // 5. retorna o resultado
        return [
            'posts' =>$posts,
            'pageCount' =>$pageCount,
            'currentPage' =>$page,
        ];
    }
}