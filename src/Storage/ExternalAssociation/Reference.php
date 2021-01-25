<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\ExternalAssociation;

class Reference
{
	/** @var string  */
	private $id;

	/** @var array  */
	private $metadata = [];

	/**
	 * @param string $id
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string|NULL $key
	 * @param mixed|NULL  $default
	 *
	 * @return array|NULL|mixed
	 */
	public function getMetadata(?string $key = NULL, $default = NULL)
	{
		if (NULL !== $key) {
			return $this->metadata[$key] ?? $default;
		}

		return $this->metadata;
	}

	/**
	 * @param array $metadata
	 *
	 * @return void
	 */
	public function setMetadata(array $metadata): void
	{
		$this->metadata = $metadata;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function addMetadata(string $key, $value): void
	{
		$this->metadata[$key] = $value;
	}

	/**
	 * @param string $key
	 *
	 * @return void
	 */
	public function removeMetadata(string $key): void
	{
		if (isset($this->metadata[$key])) {
			unset($this->metadata[$key]);
		}
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getId();
	}
}
