<?php

namespace App\Controller\Frontoffice;

use App\Model\Repository\PostRepository;
use App\Service\Email\EmailService;
use App\Service\Form\ContactFormValidator;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\View\View;
use App\Service\Http\Request;

class HomeController
{

    private View $view;
    private PostRepository $postRepository;
    private Session $session;
    private ContactFormValidator $contactFormValidator;

    public function __construct(PostRepository $postRepository, ContactFormValidator $contactFormValidator, View $view, Session $session)
    {
        $this->postRepository = $postRepository;
        $this->contactFormValidator = $contactFormValidator;
        $this->view = $view;
        $this->session = $session;
    }

    public function displayHomepageAction(Request $request, EmailService $emailService): Response
    {

        $posts = $this->postRepository->findBy([
            "post_status_fk" => 2
        ], null, 3, 0);

        $data = $request->request()->all();

        if ($request->getMethod() === 'POST') {

            if ($this->contactFormValidator->isValid($data)) {
                $mailer = $emailService->prepareEmail();
                $message = $emailService->createMessage('Sullivan Berger - Demande de contact' ,$data['email'], $data['message']);
                $mailer->send($message);
                $this->session->addFlashes('success', 'Merci pour votre message, je vous répondrai dans les meilleurs délais.');

            } else {
                $oldRequest = $request->request()->all();
                $this->session->addFlashes('error', "Le formulaire n'est pas valide, merci de vérifier les informations renseignées.");
            }
        }

        return new Response($this->view->render([
            'template' => 'home',

            'data' => [
                'posts' => $posts,
                'request' => $oldRequest,
                'connected' => $this->session->get('user')
            ],
        ]));
    }
}
