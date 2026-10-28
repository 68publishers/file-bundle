<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\HtmlStringable;
use Nette\Localization\Translator;

interface TranslatableHtmlStringInterface extends HtmlStringable
{
	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @param \Nette\Localization\Translator $translator
	 *
	 * @return void
	 */
	public function setTranslator(Translator $translator): void;
}
