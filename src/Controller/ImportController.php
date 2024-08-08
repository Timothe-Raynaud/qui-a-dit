<?php

namespace App\Controller;

use App\Form\Type\ImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import')]
    public function import(ParameterBagInterface $parameterBag): Response
    {
        $form = $this->createForm(ImportType::class);

        return $this->render('app/pages/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
