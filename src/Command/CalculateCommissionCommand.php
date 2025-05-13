<?php

namespace App\Command;

use App\Service\CsvReader;
use App\Service\CommissionCalculator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-commission')]
class CalculateCommissionCommand extends Command
{
    public function __construct(
        private readonly CsvReader   $reader,
        private readonly CommissionCalculator $calculator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'CSV file with operations');
        $this->addArgument('useHardcodedRates', InputArgument::OPTIONAL, 'use fixed given rates');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        $useHardCodedRates = false;

        if ($input->getArgument('useHardcodedRates') === '1') {
            $useHardCodedRates = true;
        }


        $operations = $this->reader->read($filename);

        foreach ($operations as $operation) {
            $fee = $this->calculator->calculate($operation, $useHardCodedRates);
            $output->writeln($fee);
        }

        return Command::SUCCESS;
    }
}
