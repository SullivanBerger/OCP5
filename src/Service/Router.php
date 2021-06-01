<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Backoffice\AdminController;
use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\UserController;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\Database\MySQLDB;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\View\View;
use Config\DotEnv;

// TODO cette classe router est un exemple très basic. Cette façon de faire n'est pas optimale
// TODO Le router ne devrait pas avoir la responsabilité de l'injection des dépendances
final class Router
{
    private MySQLDB $database;
    private View $view;
    private Request $request;
    private Session $session;

    public function __construct(Request $request)
    {
        (new DotEnv(__DIR__ . '/../../.env'))->load();

        $this->database = new MySQLDB(getenv('DATABASE_HOST'), getenv('DATABASE_NAME'), getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'));
        $this->session = new Session();
        $this->view = new View($this->session);
        $this->request = $request;
    }

    public function run(): Response
    {
        //On test si une action a été défini ? si oui alors on récupére l'action : sinon on mets une action par défaut (ici l'action posts)
        $action = $this->request->query()->has('action') ? $this->request->query()->get('action') : 'home';

        //Déterminer sur quelle route nous sommes // Attention algorithme naïf

        // *** @Route http://localhost:8000/?action=home ***
        if ($action === 'home') {
            $controller = new HomeController($this->view);

            return $controller->displayHomepageAction();
        } elseif ($action === 'posts') {
            $postRepo = new PostRepository();
            $controller = new PostController($postRepo, $this->view);

            return $controller->displayAllPostsAction();

        // *** @Route http://localhost:8000/?action=post&id=5 ***
        } elseif ($action === 'post' && $this->request->query()->has('id')) {
            //injection des dépendances et instanciation du controller
            $postRepo = new PostRepository();
            $controller = new PostController($postRepo, $this->view);

            $commentRepo = new CommentRepository($this->database);

            return $controller->displayOneAction((int) $this->request->query()->get('id'), $commentRepo);

        // *** @Route http://localhost:8000/?action=login ***
        } elseif ($action === 'login') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session);

            return $controller->loginAction($this->request);

        // *** @Route http://localhost:8000/?action=logout ***
        } elseif ($action === 'logout') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session);

            return $controller->logoutAction();
        } elseif ($action === 'admin') {
            $controller = new AdminController($this->view);

            return $controller->displayAdminHomepageAction();
        } else {
            return new Response("Error 404 - cette page n'existe pas<br><a href='index.php?action=posts'>Aller Ici</a>", 404);
        }
    }
}
