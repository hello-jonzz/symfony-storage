<?php

namespace Bluesquare\StorageBundle\Annotations;

use Doctrine\ORM\Mapping as ORM;

/**
 * @Annotation
 */
class Storage implements ORM\Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $prefix = null;

    /**
     * @var string|null
     */
    public $mode = null;

    /**
     * @var string|null
     */
    public $mime = null;
}
