<?php

namespace MuntenSite\Controller;

use Doctrine\DBAL\Connection;
use Silex\Provider;
use MuntenSite\Database\MyDB;
use Symfony\Component\HttpFoundation\Request;

class MuntController
{
    public $twig;
    public $db;

    public function __construct(\Twig_Environment $twig, Connection $db)
    {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function renderMuntPage($id)
    {
        $sql = "SELECT * FROM munten WHERE munten.ID = :id";

        $param = [
            'id' => $id
        ];


        return $this->twig->render("munten.html.twig", array('munt' => $this->db->fetchAssoc($sql, $param), ));
    }

    public function renderAddPage()
    {
        return $this->twig->render("addmunt.html.twig", array('munt' => '', ));
    }

    public function addMunt(Request $request)
    {
        $sql = 'INSERT INTO munten
            (title
            , thumbnail
            , thumbnail2
            , description
            , price
            , categorie)
            VALUES
            (:title
            , :thumbnail
            , :thumbnail2
            , :description
            , :price
            , :categorie)';

        $path = WEBROOT . "/upload/";

        $image = $request->files->get('thumbnail');
        $image->move($path, $image->getClientOriginalName());

        $image2 = $request->files->get('thumbnail2');
        $image2->move($path, $image2->getClientOriginalName());

        $munt = array(
         'title' => $request->request->get('title'),
         'thumbnail' => "/upload/" . $request->files->get('thumbnail')->getClientOriginalName(),
         'thumbnail2' => "/upload/" . $request->files->get('thumbnail2')->getClientOriginalName(),
         'description' => $request->request->get('description'),
         'price' => $request->request->getInt('euro') . "," . $request->request->getInt('cent'),
         'categorie' => $request->request->get('categorie'),
        );

        print_r($request->request->get('categorie'));

        $this->db->executeQuery($sql, $munt);

        return $this->twig->render("addedmunt.html.twig");
    }
}

