<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Options;

interface OptionsInterface
{
	/**
	 * @param string      $key
	 * @param string|NULL $validator
	 *
	 * @return mixed
	 */
	public function get(string $key, ?string $validator = NULL);

	/**
	 * @param string      $key
	 * @param string|NULL $validator
	 *
	 * @return bool
	 */
	public function has(string $key, ?string $validator = NULL): bool;

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set(string $key, $value): void;
}
