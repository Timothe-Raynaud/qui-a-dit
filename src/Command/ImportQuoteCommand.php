<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Quote;
use App\Repository\CategoryRepository;
use App\Repository\QuoteRepository;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:quote:import', description: '...')]
class ImportQuoteCommand extends Command
{
    public function __construct(private readonly FileService $fileService, private readonly ParameterBagInterface $parameterBag, private readonly KernelInterface $kernel, private readonly EntityManagerInterface $entityManager, private readonly CategoryRepository $categoryRepository, private readonly QuoteRepository $quoteRepository)
    {
        parent::__construct();
    }

    protected function configure (): void
    {
        $this
            ->setHelp('php bin/console app:quote:import filename -v')
            ->addArgument('filename', InputArgument::REQUIRED, 'The file name.');
    }

    protected function execute (InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        $filepath = $this->kernel->getProjectDir() . $this->parameterBag->get('depot_csv') . $filename . '.csv';
        $data = $this->fileService->csvConverter($filepath, ';');

        $size = count($data);
        $progress = new ProgressBar($output, $size);
        $batchSize = 20;
        $i = 1;

        if ($output->isVerbose()) {
            $progress->start();
        }

        foreach ($data as $key => $row) {
            $categoryName = $row['CATEGORIES'];
            $quoteText = $row['QUOTE'];
            $origin = $row['SOURCE'];
            $gender = $row['GENDER'];
            $hint = $row['HINT'];

            $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
            if (!$category instanceof Category){
                $category = new Category();

                $category->setName($categoryName);

                $this->entityManager->persist($category);
            }

            $quote = $this->quoteRepository->findOneBy(['text' => $quoteText]);
            if (!$quote instanceof Quote){
                $quote = new Quote();

                $quote
                    ->setCategory($category)
                    ->setOrigin($origin)
                    ->setText($quoteText)
                    ->setHint($hint)
                    ->setGender($gender);

                $this->entityManager->persist($quote);
            }

            // Display advancement
            if ($output->isVerbose()) {
                $output->writeln('<comment>--- End line : ' . $key . ' ---</comment>');
            }

            // Each 20 rows persisted we flush everything
            if (($i % $batchSize) === 0) {

                $this->entityManager->flush();
                $this->entityManager->clear();

                if ($output->isVerbose()) {
                    $progress->advance($batchSize);

                    $output->writeln(' of rows imported ... |');
                }
            }
            $i++;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return false;
    }
}
