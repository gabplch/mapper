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

namespace Rekalogika\Mapper;

use Rekalogika\Mapper\Contracts\TransformerInterface;
use Rekalogika\Mapper\Tests\Common\TestKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class RekalogikaMapperBundle extends AbstractBundle
{
    /**
     * @param array<array-key,mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        // load services

        $container->import(__DIR__ . '/../config/services.php');

        // autoconfigure services

        $builder->registerForAutoconfiguration(TransformerInterface::class)
            ->addTag('rekalogika.mapper.transformer');

        // load service configuration for test environment

        $env = $builder->getParameter('kernel.environment');

        if ($env === 'test' && class_exists(TestKernel::class)) {
            $container->import(__DIR__ . '/../config/tests.php');
        }
    }
}
