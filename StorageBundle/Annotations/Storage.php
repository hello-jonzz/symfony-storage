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
     * @var mixed
     */
    public $prefix = null;

    /**
     * @var mixed
     */
    public $mode = null;

    /**
     * @var mixed
     */
    public $mime = null;
}
