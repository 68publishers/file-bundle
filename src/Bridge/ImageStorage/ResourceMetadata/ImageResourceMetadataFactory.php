<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceMetadata;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface;

final class ImageResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function create(ResourceInterface $resource): array
	{
		$source = $resource->getSource();

		if (!$source instanceof Image) {
			return [];
		}

		return [
			MetadataName::WIDTH => $source->width(),
			MetadataName::HEIGHT => $source->height(),
			MetadataName::MIME => $source->mime(),
			MetadataName::FILE_SIZE => $source->filesize(),
		];
	}
}
