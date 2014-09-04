<?php

namespace Drupal\form_builder;

use Entity;

class FormEntity extends Entity
{

    use \GO1\FormCenter\Entity\Type\EntityTypeTrait;

    /**
     * @var integer The user id of the profile owner.
     */
    public $uid;

}
