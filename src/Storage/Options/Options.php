<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Options;

use Nette\Utils\Validators;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

final class Options implements OptionsInterface
{

	/** @var array  */
	private $options = [];

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, ?string $validator = NULL)
	{
		if (!$this->has($key)) {
			throw new InvalidStateException(sprintf(
				'Value for key "%s" not found.',
				$key
			));
		}

		if (NULL !== $validator && !Validators::is($this->options[$key], $validator)) {
			throw new InvalidStateException(sprintf(
				'Value for key "%s" doesn\'t match validator %s.',
				$key,
				$validator
			));
		}

		return $this->options[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key, ?string $validator = NULL): bool
	{
		if (!array_key_exists($key, $this->options)) {
			return FALSE;
		}

		return NULL === $validator ? TRUE : Validators::is($this->options[$key], $validator);
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, $value): void
	{
		$this->options[$key] = $value;
	}
}
