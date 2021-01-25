<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Exception;

use Nette;
use Exception;

final class UploadException extends Exception implements ExceptionInterface
{
	public const    INVALID_FILE = 1,
					UNSUPPORTED_TYPE = 2,
					MAX_FILES_REACHED = 3;

	/**
	 * @return \SixtyEightPublishers\FileBundle\Exception\UploadException
	 */
	public static function invalidFileUpload(): self
	{
		return new static(sprintf(
			'File is not instance of %s or is malformed.',
			Nette\Http\FileUpload::class
		), self::INVALID_FILE);
	}

	/**
	 * @param string $type
	 *
	 * @return \SixtyEightPublishers\FileBundle\Exception\UploadException
	 */
	public static function unsupportedType(string $type): self
	{
		return new static(sprintf(
			'Type or extension "%s" is not supported.',
			$type
		), self::UNSUPPORTED_TYPE);
	}

	/**
	 * @param int $max
	 *
	 * @return \SixtyEightPublishers\FileBundle\Exception\UploadException
	 */
	public static function maximumFilesReached(int $max): self
	{
		return new static(sprintf(
			'Maximum count of possible files (%d) reached.',
			$max
		), self::MAX_FILES_REACHED);
	}
}
