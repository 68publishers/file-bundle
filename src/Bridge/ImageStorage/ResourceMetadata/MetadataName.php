<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceMetadata;

use SixtyEightPublishers\FileBundle\ResourceMetadata\MetadataName as FileMetadataName;

/**
 * Enum with names of basic metadata.
 */
class MetadataName extends FileMetadataName
{
	public const WIDTH = 'width';
	public const HEIGHT = 'height';
	public const NUMBER_OF_FRAMES = 'number_of_frames';
}
