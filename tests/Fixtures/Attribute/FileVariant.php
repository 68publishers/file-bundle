<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Fixtures\Attribute;

use Doctrine\ORM\Mapping as ORM;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

/**
 * An application entity mapped with attributes that relates to the file entity.
 */
#[ORM\Entity]
class FileVariant
{
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: FileInterface::class)]
	#[ORM\JoinColumn(onDelete: 'CASCADE', nullable: FALSE)]
	private FileInterface $file;

	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 50)]
	private string $name;

	public function __construct(FileInterface $file, string $name)
	{
		$this->file = $file;
		$this->name = $name;
	}
}
