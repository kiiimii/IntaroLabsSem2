<?php
namespace BazaraJack\Library\Controller;

use BazaraJack\Library\Core\View;
use BazaraJack\Library\Model\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthController{
    
    public function showLoginForm(Request $request){

        $session = $request->getSession();
        $error = $session->get('auth_error');
        $session->remove('auth_error');

        $html = View::getTwig()->render('/auth/auth.html.twig',[
            'error'=>$error,
            'isAuth'=> false
        ]);

        return new Response($html);
    }

    public function login(Request $request){
        $email = $request->request->get('email');
        $password = ($request->request->get('password'));

        $db = Database::getConnection();
        $sql = "SELECT email, password FROM users WHERE email=:email";

        $stmt = $db->prepare($sql);
        $stmt->execute(["email"=>$email]);

        $user = $stmt->fetch();

        $session = $request->getSession();

        if($user && password_verify($password,$user['password'])){
            $session->set('user',$user['email']);

            return new RedirectResponse('/');
        };

        $session->set('auth_error', 'Неверный логин или пароль');

        return new RedirectResponse("/login");
    }

    public function logout(Request $request){
            $request->getSession()->invalidate();
            return new RedirectResponse('/');
    }


}
