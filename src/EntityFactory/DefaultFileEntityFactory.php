<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\EntityFactory;

use SixtyEightPublishers\FileStorage\FileInfoInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

final class DefaultFileEntityFactory implements FileEntityFactoryInterface
{
	/** @var string  */
	private $className;

	/**
	 * @param string $className
	 */
	public function __construct(string $className)
	{
		$this->className = $className;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(FileInfoInterface $fileInfo): FileInterface
	{
		$class = $this->className;

		return new $class($fileInfo);
	}
}
