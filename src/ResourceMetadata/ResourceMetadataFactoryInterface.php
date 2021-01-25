<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\ResourceMetadata;

use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;

interface ResourceMetadataFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\FileStorage\Resource\ResourceInterface $resource
	 *
	 * @return array
	 */
	public function create(ResourceInterface $resource): array;
}
