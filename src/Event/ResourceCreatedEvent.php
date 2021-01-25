<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;

final class ResourceCreatedEvent extends Event
{
	public const NAME = 'file_bundle.resource_created';

	/** @var \SixtyEightPublishers\FileStorage\Resource\ResourceInterface  */
	private $resource;

	/**
	 * @param \SixtyEightPublishers\FileStorage\Resource\ResourceInterface $resource
	 */
	public function __construct(ResourceInterface $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * @return \SixtyEightPublishers\FileStorage\Resource\ResourceInterface
	 */
	public function getResource(): ResourceInterface
	{
		return $this->resource;
	}
}
