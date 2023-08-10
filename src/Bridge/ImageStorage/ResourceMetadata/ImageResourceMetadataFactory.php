<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceMetadata;

use Imagick;
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

		$core = $source->getCore();

		return [
			MetadataName::WIDTH => $source->width(),
			MetadataName::HEIGHT => $source->height(),
			MetadataName::NUMBER_OF_FRAMES => $core instanceof Imagick ? $core->getNumberImages() : 1,
			MetadataName::MIME => $source->mime(),
			MetadataName::FILE_SIZE => $source->filesize(),
		];
	}
}
