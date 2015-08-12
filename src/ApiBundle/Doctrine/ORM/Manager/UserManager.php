<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Doctrine\ORM\Manager;

use ApiBundle\Entity\User;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UserManager extends BaseUserManager implements EntityManagerInterface
{
    /**
     * Deletes the entity.
     *
     * @param UserInterface $entity
     */
    public function delete($entity)
    {
        $this->deleteUser($entity);
    }

    /**
     * Updates the entity.
     *
     * @param UserInterface $entity
     */
    public function update($entity)
    {
        $this->updateUser($entity, false);
    }

    /**
     * Checks whether the given class is supported by this manager.
     *
     * @param $entity
     *
     * @return bool
     */
    public function supports($entity)
    {
        return $entity instanceof UserInterface;
    }


    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        // Unset relations before actually removing the user
        if ($user instanceof User) {
            $jobs = $user->getJobs();
            foreach ($jobs as $job) {
                $job->removeUser($user);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        parent::updateCanonicalFields($user);

        if ($user instanceof User) {
            $user->setOrganizationEmailCanonical($this->canonicalizeEmail($user->getOrganizationEmail()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        // Extract email part before the `@` character to use it as username is username not set
        if (null === $user->getUsername()) {
            $user->setUsername(substr($user->getEmail(), 0, strpos($user->getEmail(), '@')));
        }

        // Call parent after as does not override parent and parent do the flush
        parent::updateUser($user, $andFlush);
    }
}
