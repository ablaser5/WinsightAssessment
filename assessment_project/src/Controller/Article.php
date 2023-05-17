<?php

namespace App\Controller;

use App\Service\JsonLoader;
use App\Service\ArticleParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Article extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('gallery', ['category' => 'technology']);
    }
}

