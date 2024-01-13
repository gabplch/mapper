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

use Rekalogika\Mapper\Transformer\Contracts\MixedType;
use Rekalogika\Mapper\TransformerRegistry\TransformerRegistryInterface;
use Rekalogika\Mapper\TypeResolver\TypeResolverInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

#[AsCommand(name: 'rekalogika:mapper:tryproperty', description: 'Gets the mapping result by providing the class and property name of the source and target.')]
class TryPropertyCommand extends Command
{
    public function __construct(
        private TransformerRegistryInterface $transformerRegistry,
        private TypeResolverInterface $typeResolver,
        private PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sourceClass', InputArgument::REQUIRED, 'The source class')
            ->addArgument('sourceProperty', InputArgument::REQUIRED, 'The source property')
            ->addArgument('targetClass', InputArgument::REQUIRED, 'The target class')
            ->addArgument('targetProperty', InputArgument::REQUIRED, 'The target property')
            ->setHelp("The <info>%command.name%</info> displays the mapping result by providing the class and property name of the source and target.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = [];

        //
        // source type
        //

        /** @var string */
        $sourceClass = $input->getArgument('sourceClass');
        /** @var string */
        $sourceProperty = $input->getArgument('sourceProperty');
        /** @var string */
        $targetClass = $input->getArgument('targetClass');
        /** @var string */
        $targetProperty = $input->getArgument('targetProperty');

        $sourceTypes = $this->propertyInfoExtractor
            ->getTypes($sourceClass, $sourceProperty);

        if ($sourceTypes === null || count($sourceTypes) === 0) {
            $sourceTypes = [MixedType::instance()];
        }

        $targetTypes = $this->propertyInfoExtractor
            ->getTypes($targetClass, $targetProperty);

        if ($targetTypes === null || count($targetTypes) === 0) {
            $targetTypes = [MixedType::instance()];
        }


        $results = $this->transformerRegistry
            ->findBySourceAndTargetTypes($sourceTypes, $targetTypes);

        foreach ($results as $result) {
            $rows[] = [
                $result->getMappingOrder(),
                $this->typeResolver->getTypeString($result->getSourceType()),
                $this->typeResolver->getTypeString($result->getTargetType()),
                $result->getTransformer()::class,
            ];
        }

        //
        // render
        //

        $io->section('<info>Applicable Transformers</info>');

        if (count($rows) === 0) {
            $io->error('No applicable transformers found.');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setVertical();
        $table->setHeaders(['Mapping Order', 'Source Type', 'Target Type', 'Transformer']);
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
}
