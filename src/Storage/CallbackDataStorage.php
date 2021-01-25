<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class CallbackDataStorage implements DataStorageInterface
{
	use DataStorageTrait;

	/** @var callable  */
	protected $callback;

	/**
	 * @param callable $callback
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFiles(): Collection
	{
		$cb = $this->callback;

		return new ArrayCollection((array) $cb());
	}
}
