<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/mapper package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Mapper\Command;

use Rekalogika\Mapper\MainTransformer;
use Rekalogika\Mapper\TypeStringHelper;
use Rekalogika\Mapper\Util\TypeFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'debug:mapper:try', description: 'Gets the mapping result from a source and target type pair.')]
class TryCommand extends Command
{
    public function __construct(
        private MainTransformer $mainTransformer,
        private TypeStringHelper $typeStringHelper
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'The source type')
            ->addArgument('target', InputArgument::REQUIRED, 'The target type')
            ->setHelp("The <info>%command.name%</info> displays the mapping result from a source type and a target type.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = [];

        //
        // source type
        //

        /** @var string */
        $sourceTypeString = $input->getArgument('source');
        $sourceType = TypeFactory::fromString($sourceTypeString);
        $sourceTypeStrings = $this->typeStringHelper
            ->getApplicableTypeStrings($sourceType);

        $rows[] = ['Source type', $sourceTypeString];
        $rows[] = new TableSeparator();
        $rows[] = [
            'Transformer source types compatible with source',
            implode("\n", $sourceTypeStrings)
        ];

        //
        // target type
        //

        /** @var string */
        $targetTypeString = $input->getArgument('target');
        $targetType = TypeFactory::fromString($targetTypeString);
        $targetTypeStrings = $this->typeStringHelper
            ->getApplicableTypeStrings($targetType);

        $rows[] = new TableSeparator();
        $rows[] = ['Target type', $targetTypeString];
        $rows[] = new TableSeparator();
        $rows[] = [
            'Transformer target types compatible with target',
            implode("\n", $targetTypeStrings)
        ];

        //
        // render
        //

        $io->section('<info>Type Compatibility</info>');
        $table = new Table($output);
        $table->setHeaders(['Subject', 'Value']);
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();

        //
        // get applicable transformers
        //

        $rows = [];

        $transformers = $this->mainTransformer->getTransformerMapping(
            $sourceType,
            $targetType
        );

        foreach ($transformers as $entry) {
            $rows[] = [
                $entry->getOrder(),
                $entry->getId(),
                $entry->getClass(),
                $entry->getSourceType(),
                $entry->getTargetType()
            ];
            $rows[] = new TableSeparator();
        }

        array_pop($rows);

        //
        // render
        //

        $io->writeln('');
        $io->section('<info>Applicable Transformers</info>');

        if (count($rows) === 0) {
            $io->error('No applicable transformers found.');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Order', 'Service ID', 'Class', 'Source Type', 'Target Type']);
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
}
