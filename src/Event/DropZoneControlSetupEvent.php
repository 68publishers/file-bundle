<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl;

final class DropZoneControlSetupEvent extends Event
{
	public const NAME = 'file_bundle.drop_zone_control_setup';

	/** @var \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl  */
	private $dropZoneControl;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl $dropZoneControl
	 */
	public function __construct(DropZoneControl $dropZoneControl)
	{
		$this->dropZoneControl = $dropZoneControl;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl
	 */
	public function getDropZoneControl(): DropZoneControl
	{
		return $this->dropZoneControl;
	}
}
