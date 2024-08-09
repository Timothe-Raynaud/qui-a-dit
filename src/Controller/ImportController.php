<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Type\ImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import')]
    public function import(Request $request, KernelInterface $kernel, ParameterBagInterface $parameterBag, SluggerInterface $slugger,
                           #[Autowire('%kernel.project_dir%/public/uploads/import')] string $importDirectory): Response
    {
        $form = $this->createForm(ImportType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get Data
            $data = $form->getData();
            $password = $data['password'];
            $file = $data['file'];

            if (($password === $parameterBag->get('import_key')) && $file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid('', true).'.csv';
                try {
                    $file->move($importDirectory, $newFilename);
                } catch (FileException $e) {

                }

                $process = new Process(['php', 'bin/console', 'app:quote:import', $newFilename]);
                $process->setWorkingDirectory($kernel->getProjectDir());
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                $this->addFlash('success', 'Fichier importé !');
                return $this->redirectToRoute('app_home');
            }
        }


        return $this->render('app/pages/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
