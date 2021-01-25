<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\ResourceMetadata;

/**
 * Enum with names of basic metadata.
 */
class MetadataName
{
	public const MIME = 'mime';
	public const NAME = 'name';
	public const DESCRIPTION = 'description';
	public const FILE_SIZE = 'file_size';

	private function __construct()
	{
	}
}
