<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity;

use DateTimeInterface;
use SixtyEightPublishers\FileStorage\FileInfoInterface;

interface FileInterface
{
	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @return \SixtyEightPublishers\FileStorage\FileInfoInterface
	 */
	public function getSource(): FileInfoInterface;

	/**
	 * @return \DateTimeInterface
	 */
	public function getCreated(): DateTimeInterface;

	/**
	 * @param string|NULL $key
	 * @param mixed|NULL  $default
	 *
	 * @return mixed|array|NULL
	 */
	public function getMetadata(string $key = NULL, $default = NULL);

	/**
	 * @param array $metadata
	 *
	 * @return void
	 */
	public function setMetadata(array $metadata): void;

	/**
	 * Updates the version of a source and a `updated` field
	 *
	 * @return void
	 */
	public function update(): void;
}
