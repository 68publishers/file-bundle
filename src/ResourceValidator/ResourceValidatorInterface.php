<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\ResourceValidator;

use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;

interface ResourceValidatorInterface
{
	/**
	 * @param \SixtyEightPublishers\FileStorage\Resource\ResourceInterface $resource
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface
	 */
	public function validate(ResourceInterface $resource): void;
}
