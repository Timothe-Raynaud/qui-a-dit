<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Quote;
use App\Repository\CategoryRepository;
use App\Repository\QuoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GameController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('app/pages/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/question/{slug}', name: 'app_question')]
    public function question(Category $category, QuoteRepository $quoteRepository, RequestStack $request): Response
    {
        $session = $request->getSession();
        $pastQuote = $session->get('pastQuote', []);
        $quotes = $quoteRepository->findBy(['category' => $category]);

        if (!empty($pastQuote[$category->getId()])){
            do {
                if ($quotes === []){
                    $quote = null;
                    break;
                }
                $randId = array_rand($quotes);
                $quote = $quotes[$randId];
                unset($quotes[$randId]);
            } while (in_array($quote->getId(), $pastQuote[$category->getId()], true));
        } else{
            $randId = array_rand($quotes);
            $quote = $quotes[$randId];
        }

        if (!$quote instanceof Quote){
            return $this->render('app/pages/no-more.html.twig');
        }

        $pastQuote[$category->getId()][] = $quote->getId();
        $session->set('pastQuote', $pastQuote);

        return $this->render('app/pages/question.html.twig', [
            'quote' => $quote
        ]);
    }
}
