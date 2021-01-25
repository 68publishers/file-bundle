<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\ResourceMetadata;

use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;

final class ResourceMetadataFactoryRegistry implements ResourceMetadataFactoryInterface
{
	/** @var \SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface[] */
	private $factories;

	/**
	 * @param \SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface[] $factories
	 */
	public function __construct(array $factories)
	{
		$this->factories = (static function (ResourceMetadataFactoryInterface ...$factories) {
			return $factories;
		})(...$factories);
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(ResourceInterface $resource): array
	{
		$metadata = [[]];

		foreach ($this->factories as $factory) {
			$metadata[] = $factory->create($resource);
		}

		return array_merge(...$metadata);
	}
}
