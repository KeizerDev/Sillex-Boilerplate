<?php

namespace MuntenSite\Controller;

use Doctrine\DBAL\Connection;
use PDO;
use Silex\Provider;

class CategorieController
{
    public $twig;
    public $db;

    public function __construct(\Twig_Environment $twig, Connection $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function renderCategoriePage($categorie)
    {
        $sql = "SELECT * FROM munten WHERE munten.categorie =:categorie";

        $param = [
            'categorie' => $categorie
        ];

        $muntenArr = $this->db->fetchAll($sql, $param);
        return $this->twig->render("home.html.twig", array('munten' => $muntenArr, ));
    }
}

