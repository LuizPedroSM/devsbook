<?php
namespace src\handlers;

use \src\models\Post;
use \src\models\PostLike;
use \src\models\PostComment;
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

    public function _postListToObject($postList,int $loggedUserId): array
    {
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
            if ($postItem['id_user'] == $loggedUserId) {
                $newPost->mine = true;
            }

            // 4. preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];
            
            // TODO: 4.1 preencher as informações de LIKE
            $likes = PostLike::select()->where('id_post', $postItem['id'])->get();
            $newPost->likeCount = count($likes);
            $newPost->liked = self::isLiked($postItem['id'], $loggedUserId);
            // TODO: 4.2 preencher as informações de COMMENTS
            $newPost->comments = PostComment::select()->where('id_post', $postItem['id'])->get();
            foreach ($newPost->comments as $key => $comment) {
                $newPost->comments[$key]['user'] = User::select()->where('id', $comment['id_user'])->one();
            }

            $posts[] = $newPost;
        }
        return $posts;
    }

    public static function isLiked($postId, $loggedUserId)
    {
        $myLike = PostLike::select()
        ->where('id_post', $postId)
        ->where('id_user', $loggedUserId)
        ->get();
        
        return (count($myLike) > 0)? true : false;
    }

    public static function deleteLike($postId, $loggedUserId)
    {
        PostLike::delete()
            ->where('id_post',$postId)
            ->where('id_user',$loggedUserId)
        ->execute();

    }
    
    public static function addLike($postId, $loggedUserId)
    {
        PostLike::insert([
            'id_post' => $postId,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
        ])->execute();
    }

    public static function addComment($postId, $txt, $loggedUserId)
    {
        PostComment::insert([
            'id_post' => $postId,
            'id_user' => $loggedUserId,
            'body' => $txt,
            'created_at' => date('Y-m-d H:i:s')
        ])->execute();
    }

    public static function getHomeFeed(int $idUser,int $page)
    {
        $perPage = 10;
        // 1. Pegar lista de usuários que EU sigo.
        $userList = UserRelation::select()
            ->where('user_from', $idUser)
        ->get();
        
        $users = [$idUser];
        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
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
        $posts = self::_postListToObject($postList, $idUser);
        
        // 5. retorna o resultado
        return [
            'posts' =>$posts,
            'pageCount' =>$pageCount,
            'currentPage' =>$page,
        ];
    }

    public static  function getUserFeed(int $idUser, int $page, int $loggedUserId)
    {
        $perPage = 10;
        
        // 2. Pegar os posts dessa galera ordenado pela data.
        $postList = Post::select()
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', $idUser)
        ->count();
        $pageCount = ceil($total/$perPage);

        // 3. Transformar o resultado em objetos dos models
        $posts = self::_postListToObject($postList, $loggedUserId);
        
        // 5. retorna o resultado
        return [
            'posts' =>$posts,
            'pageCount' =>$pageCount,
            'currentPage' =>$page,
        ];
    }

    public static function getPhotosFrom(int $idUser): array
    {
        $photosData = Post::select()
            ->where('id_user', $idUser)
            ->where('type', 'photo')
        ->get();

        $photos = [];

        foreach ($photosData as $photo) {
            $newPost = new Post();
            $newPost->id = $photo['id'];
            $newPost->photo = $photo['type'];
            $newPost->created_at = $photo['created_at'];
            $newPost->body = $photo['body'];

            $photos[] = $newPost;
        }
        return $photos;
    }

    public static function delete($id, $loggedUserId)
    {
        // 1. verificar se o post existe e é seu
        $post = Post::select()->where('id', $id)->where('id_user', $loggedUserId)->get();
        if (count($post) > 0) {
            $post = $post[0];
            // 2. deletar os likes e coments
            PostLike::delete()->where('id_post', $id)->execute();
            PostComment::delete()->where('id_post', $id)->execute();
            
            // 3. se a post for type == photo, deletar o arquivo
            if($post['type'] === 'photo'){
                $img = __DIR__.'/../../public/media/uploads/'.$post['body'];
                if (file_exists($img)) {
                    unlink(($img));
                }
            }
            // 4. deletar o post
            Post::delete()->where('id', $id)->execute();
        }
    }
}