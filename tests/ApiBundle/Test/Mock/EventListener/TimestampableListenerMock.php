<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Test\Mock\EventListener;

use Doctrine\Common\NotifyPropertyChanged;
use Gedmo\Timestampable\TimestampableListener;

/**
 * Class GeneratorMock: mocking class for {@see Gedmo\Timestampable\TimestampableListener}. This mock set createAt and
 * updatedAt values at a fixed value in order to ease the testing.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class TimestampableListenerMock extends TimestampableListener
{
    /**
     * {@inheritdoc}
     */
    protected function getNamespace()
    {
        // Put original namespace as it is used to load other classes such as the mapping drive annotation
        return 'Gedmo\\Timestampable';
    }

    /**
     * {@inheritdoc}
     *
     * createdAt value set at : 2015-01-01 00:00:00.000000
     * updatedAt value set at : 2015-06-10 00:00:00.000000
     */
    protected function updateField($object, $ea, $meta, $field)
    {
        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);
        $newValue = $ea->getDateValue($meta, $field);
        $property->setValue($object, $newValue);

        // Added block
        switch ($field) {
            case 'createdAt':
                $newValue->setDate(2015, 01, 01);
                $newValue->setTime(0, 0, 0);
                break;

            case 'updatedAt':
                $newValue->setDate(2015, 06, 10);
                $newValue->setTime(0, 0, 0);
                break;
        }
        // End of the added block

        if ($object instanceof NotifyPropertyChanged) {
            $uow = $ea->getObjectManager()->getUnitOfWork();
            $uow->propertyChanged($object, $field, $oldValue, $newValue);
        }
    }
}
