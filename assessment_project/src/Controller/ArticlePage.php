<?php

namespace App\Controller;

use App\Service\JsonLoader;
use App\Service\ArticleParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlePage extends AbstractController
{
    /**
     * @Route("/articles/{category}/{title}", name="article")
     */
    public function article_page(JsonLoader $jsonLoader, ArticleParser $articleparser, $category="", $title=""): Response
    {
        $articles = $articleparser->parse();
        $article = null;

        // Handle a few edge cases
        $category = str_replace('-', ' ', $category);

        // Set the article for the page, but only if it exists in the data
        foreach ($articles['articles'][$category]['articles'] as $a) {
            if ($category === 'emerging brands'){
                $category = 'emerging-brands';
            }
            if ($a['uri'] == ('/'.$category.'/'.$title)) {
                $article = $a;
                break;
            }
        }

        if ($article === null) {
            throw $this->createNotFoundException('Article not found');
        }

        // Get byline data
        $authors = null;
        if (count($article['field_byline']) > 0) {
            $authors = array_map(function($author) {
                return [
                    'title' => $author['title'] ?? '',
                    'uri' => $author['uri'] ?? ''
                ];
            }, $article['field_byline']);
        }

        $article["authors"] = $authors;
        $article["date"] = $article["field_published_date"];

        // Get articles in the same category to show in sidebar
        $more_articles = null;
        if (array_key_exists($category, $articles['articles'])) {
            $more_articles = $articles['articles'][$category]['previews'];
        }
        
        return $this->render('article.html.twig', [
            'node'=> $article,
            'articles' => $more_articles,
            'categories'=> $articles['categories']
        ]);
    }
}