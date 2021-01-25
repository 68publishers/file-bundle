<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Helper;

use Nette\Utils\Strings;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;

final class MaxFileSize
{
	public function __construct()
	{
	}

	/**
	 * @return int
	 */
	public static function getDefault(): int
	{
		return self::parseBytes(ini_get('upload_max_filesize'));
	}

	/**
	 * @param int|float|string $value
	 *
	 * @return int
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	public static function parseBytes($value): int
	{
		if (is_numeric($value)) {
			return (int) $value;
		}

		$value = Strings::trim($value);
		$numValue = Strings::substring($value, 0, Strings::length($value) - 1);

		switch (Strings::lower(Strings::substring($value, Strings::length($value) - 1))) {
			case 'g':
				$parsed = $numValue * (1024 * 1024 * 1024); //1073741824

				break;
			case 'm':
				$parsed = $numValue * (1024 * 1024); //1048576

				break;
			case 'k':
				$parsed = $numValue * 1024;

				break;
		}
		if (isset($parsed)) {
			return (int) floor($parsed);
		}

		throw new InvalidArgumentException(sprintf(
			'Argument 1 passed to %s is not valid.',
			__METHOD__
		));
	}
}
