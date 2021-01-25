<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Exception;

use Exception;
use Throwable;

final class FileManipulationException extends Exception implements ExceptionInterface
{
	/**
	 * @param string          $manipulationType
	 * @param int             $code
	 * @param \Throwable|NULL $previous
	 *
	 * @return \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 */
	public static function error(string $manipulationType, int $code = 0, ?Throwable $previous = NULL): self
	{
		return new static(sprintf(
			'Error during manipulation %s on file.',
			$manipulationType
		), $code, $previous);
	}
}
