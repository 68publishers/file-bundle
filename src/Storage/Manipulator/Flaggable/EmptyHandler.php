<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;

final class EmptyHandler implements FlagHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function canHandle(FileInterface $file): bool
	{
		return TRUE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, bool $unique = FALSE): void
	{
	}
}
