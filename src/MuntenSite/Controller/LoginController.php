<?php

namespace MuntenSite\Controller;

use Doctrine\DBAL\Connection;
use Silex\Provider;

class LoginController
{
    public $twig;
    public $db;

    public function __construct(\Twig_Environment $twig, Connection $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function renderLoginPage()
    {
        return $this->twig->render("login.html.twig", array('error' => null, ));
    }
}

