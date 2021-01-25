<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\EntityFactory;

use SixtyEightPublishers\FileStorage\FileInfoInterface;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

interface FileEntityFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\FileStorage\FileInfoInterface $fileInfo
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 */
	public function create(FileInfoInterface $fileInfo): FileInterface;
}
