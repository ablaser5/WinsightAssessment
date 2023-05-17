<?php

namespace App\Controller;

use App\Service\JsonLoader;
use App\Service\ArticleParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleCategoryPage extends AbstractController
{
    /**
     * @Route("/article/{category}", name="gallery")
     */
    public function article_gallery(JsonLoader $jsonLoader, ArticleParser $articleparser, $category = "technology"): Response
    {
        $a = $articleparser->parse();
        
        // Handles a couple edge cases with the data
        $category = str_replace('-', ' ', $category);
        
        // Check if the category exists
        if (!isset($a['articles'][$category])) {
            throw $this->createNotFoundException('Category not found');
        }
        
        // Get three random articles for the left side of the gallery page
        $random_articles = [];
        $random_article_count = min(3, count($a['all_articles']));
        $keys = array_rand($a['all_articles'], $random_article_count);
        for ($i = 0; $i < $random_article_count; $i++) {
            $random_articles[] = $a['all_articles'][$keys[$i]];
        }

        return $this->render('pages/gallery.html.twig', [
            'featured' => $random_articles,
            'articles' => $a['articles'][$category]['previews'],
            'category' => $category,
            'categories' => $a['categories']
        ]);
    }
}

