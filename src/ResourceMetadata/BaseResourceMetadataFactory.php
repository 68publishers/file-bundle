<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\ResourceMetadata;

use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;

final class BaseResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function create(ResourceInterface $resource): array
	{
		$source = $resource->getSource();

		if (is_resource($source)) {
			$meta = stream_get_meta_data($source);

			return [
				MetadataName::MIME => mime_content_type($source),
				MetadataName::FILE_SIZE => isset($meta['uri']) ? (int) filesize($meta['uri']) : 0,
			];
		}

		return [];
	}
}
