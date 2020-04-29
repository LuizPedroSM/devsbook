<?=$render('header', ['loggedUser' => $loggedUser]);?>

<section class="container main">

    <?=$render('sidebar', ['activeMenu' => 'settings']);?>
    <section class="feed mt-10">
        <div class="row">
            <div class="column pr-5">
                <h1>Configurações</h1>
                <?php if(!empty($flash)):?>
                <div class="flash"><?=$flash?></div>
                <?php endif;?>
                <form method="POST" action="<?=$base;?>/settings">
                    <label for="avatar">
                        Novo Avatar: <br>
                        <input type="file" name="avatar" id="avatar">
                    </label><br>
                    <label for="capa">
                        Nova Capa: <br>
                        <input type="file" name="capa" id="capa">
                    </label><br>

                    <hr />
                    <label for="name">
                        Nome Completo: <br>
                        <input type="text" name="name" id="name" value="<?=$user->name?>">
                    </label><br>
                    <label for="birthdate">
                        Data de nascimento: <br>
                        <input type="date" name="birthdate" id="birthdate" value="<?=$user->birthdate?>">
                    </label><br>
                    <label for="email">
                        E-mail: <br>
                        <input type="email" name="email" id="email" value="<?=$user->email?>">
                    </label><br>
                    <label for="city">
                        Cidade: <br>
                        <input type="text" name="city" id="city" value="<?=$user->city?>">
                    </label><br>
                    <label for="work">
                        Trabalho: <br>
                        <input type="text" name="work" id="work" value="<?=$user->work?>">
                    </label><br>

                    <hr />
                    <label for="newPassword">
                        Nova Senha: <br>
                        <input type="password" name="newPassword" id="newPassword">
                    </label><br>
                    <label for="confirmPassword">
                        Confirma Nova Senha: <br>
                        <input type="password" name="confirmPassword" id="confirmPassword">
                    </label><br>
                    <input class="button" type="submit" value="Salvar">
                </form>
            </div>
        </div>
    </section>
</section>
<?=$render('footer');?>