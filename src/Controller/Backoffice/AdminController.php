<?php

namespace App\Controller\Backoffice;

use App\Service\Http\Response;
use App\View\View;

class AdminController
{
    /**
     * @var View
     */
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function displayAdminHomepageAction()
    {
        return new Response($this->view->render([
            'template' => '../backoffice/admin_homepage',
        ]));
    }
}