<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone;

interface DropZoneControlFactoryInterface
{
	/**
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function create(): DropZoneControl;
}
