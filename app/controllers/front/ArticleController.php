<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Models\Article;
// use App\Core\Log; 

class ArticleController extends Controller
{
    public function index()
    {
        // try {
            $articleModel = new Article();
            $articles = $articleModel->getAll();

            $data = [
                'title' => 'Liste des articles',
                'articles' => $articles
            ];

            echo View::render('front/index.twig', $data);
        // } catch (\Exception $e) {
        //     Log::getLogger()->critical('Exception lors de l\'affichage de la liste des articles : ' . $e->getMessage(), ['exception' => $e]);
        //     http_response_code(500);
        //     echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        // }
    }

    public function show(string $id)
    {
        // try {
            $articleModel = new Article();
            $article = $articleModel->getById($id);

            if (!$article) {
                echo "Article non trouvé"; 
                return;
            }

            $data = [
                'title' => $article->getTitle(),
                'article' => $article
            ];

            echo View::render('front/show.twig', $data);
        // } catch (\Exception $e) {
        //     Log::getLogger()->critical('Exception lors de l\'affichage de l\'article : ' . $e->getMessage(), ['article_id' => $id, 'exception' => $e]);
        //     http_response_code(500);
        //     echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        // }
    }
}