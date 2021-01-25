<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\Utils\Html;

final class Title implements TranslatableHtmlStringInterface
{
	use TranslatableHtmlStringTrait;

	/** @var string  */
	private $title;

	/** @var bool  */
	private $translate;

	/**
	 * @param string $title
	 * @param bool   $translate
	 */
	public function __construct(string $title = 'default_title', bool $translate = TRUE)
	{
		$this->title = $title;
		$this->translate = $translate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$html = Html::el('span')
			->setText($this->translate ? $this->getTranslator()->translate($this->title) : $this->title);

		return (string) $html;
	}
}
