<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity\SoftDeletable;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SixtyEightPublishers\FileBundle\Entity\AbstractFile;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletableFileTrait;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletableFileInterface;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_FILE_CREATED", columns={"created"})
 * })
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
final class File extends AbstractFile implements SoftDeletableFileInterface
{
	use SoftDeletableFileTrait;
}
