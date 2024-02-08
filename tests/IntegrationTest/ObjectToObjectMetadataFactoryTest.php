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

namespace Rekalogika\Mapper\Tests\IntegrationTest;

use Rekalogika\Mapper\Context\Context;
use Rekalogika\Mapper\Tests\Common\AbstractFrameworkTest;
use Rekalogika\Mapper\Tests\Fixtures\Scalar\ObjectWithScalarProperties;
use Rekalogika\Mapper\Tests\Fixtures\ScalarDto\ObjectWithScalarPropertiesDto;
use Rekalogika\Mapper\Transformer\ObjectToObjectMetadata\ObjectToObjectMetadataFactoryInterface;

class ObjectToObjectMetadataFactoryTest extends AbstractFrameworkTest
{
    public function testObjectToObjectMetadataFactory(): void
    {
        $factory = $this->get('rekalogika.mapper.object_to_object_metadata_factory');
        $this->assertInstanceOf(ObjectToObjectMetadataFactoryInterface::class, $factory);

        $metadata = $factory->createObjectToObjectMetadata(
            ObjectWithScalarProperties::class,
            ObjectWithScalarPropertiesDto::class,
            Context::create()
        );

        $this->assertEquals(ObjectWithScalarProperties::class, $metadata->getSourceClass());
        $this->assertEquals(ObjectWithScalarPropertiesDto::class, $metadata->getTargetClass());
        $this->assertCount(4, $metadata->getPropertyMappings());
    }

    public function testProxyResolving(): void
    {
        $factory = $this->get('rekalogika.mapper.object_to_object_metadata_factory');
        $this->assertInstanceOf(ObjectToObjectMetadataFactoryInterface::class, $factory);

        // @phpstan-ignore-next-line
        eval(<<<'PHP'
namespace Proxies\__CG__\Rekalogika\Mapper\Tests\Fixtures\Scalar;

class ObjectWithScalarProperties extends \Rekalogika\Mapper\Tests\Fixtures\Scalar\ObjectWithScalarProperties {
}
PHP);

        $metadata = $factory->createObjectToObjectMetadata(
            // @phpstan-ignore-next-line
            'Proxies\\__CG__\\Rekalogika\\Mapper\\Tests\\Fixtures\\Scalar\\ObjectWithScalarProperties',
            ObjectWithScalarPropertiesDto::class,
            Context::create()
        );

        $this->assertEquals(ObjectWithScalarProperties::class, $metadata->getSourceClass());
        $this->assertEquals(ObjectWithScalarPropertiesDto::class, $metadata->getTargetClass());
        $this->assertCount(4, $metadata->getPropertyMappings());
    }
}
