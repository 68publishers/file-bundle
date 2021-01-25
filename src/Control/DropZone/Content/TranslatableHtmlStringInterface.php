<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\Utils\IHtmlString;
use Nette\Localization\ITranslator;

interface TranslatableHtmlStringInterface extends IHtmlString
{
	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 *
	 * @return void
	 */
	public function setTranslator(ITranslator $translator): void;
}
