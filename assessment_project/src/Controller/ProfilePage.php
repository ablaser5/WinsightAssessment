<?php

namespace App\Controller;

use App\Service\JsonLoader;
use App\Service\ArticleParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilePage extends AbstractController
{
    /**
     * @Route("/profile/{uri}", name="profile")
     */
    public function article_page(JsonLoader $jsonLoader, ArticleParser $articleparser, $uri=""): Response
    {
        $a = $articleparser->parse();

        $authors = $a['authors'];
        
        return $this->render('pages/profile.html.twig', [
            'profile'=> $authors['/profile/'.$uri],
            'categories'=> $a['categories']
        ]);
    }
}