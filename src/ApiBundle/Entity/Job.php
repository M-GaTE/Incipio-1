<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\ApiBundle\Annotation\Iri;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity
 * @ORM\EntityListeners({"ApiBundle\EventListener\Doctrine\JobListener"})
 * @UniqueEntity("title")
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Job
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string Job title abbreviation.
     *
     * @Iri("https://schema.org/alternateName")
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(max=20)
     * @Groups({"job", "user"})
     */
    private $abbreviation;

    /**
     * @var bool If false the job cannot be used anymore.
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     * @Assert\NotNull
     * @Groups({"job", "user"})
     */
    private $enabled = true;

    /**
     * @var Mandate
     *
     * @ORM\ManyToOne(targetEntity="Mandate", inversedBy="jobs")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Groups({"job", "user"})
     */
    private $mandate;

    /**
     * @var string
     *
     * @Iri("http://schema.org/roleName")
     * @ORM\Column(type="string", length=255)
     * @Assert\Type("string")
     * @Assert\Length(min=2, max=100)
     * @Assert\NotBlank
     * @Groups({"job", "user"})
     */
    private $title;

    /**
     * @var ArrayCollection|User[]
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="jobs")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Groups({"job"})
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $abbreviation
     *
     * @return $this
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param Mandate|null $mandate
     *
     * @return $this
     */
    public function setMandate(Mandate $mandate = null)
    {
        // Handle bidirectional relationship
        // Check for the other side first
        if (null === $mandate && null !== $this->mandate && true === $this->mandate->getJobs()->contains($this)) {
            // Mandate was set and is being reset
            // Since it's a bidirectional relationship, unset the other relation from the other side first
            $this->mandate->removeJob($this);
        } elseif (null !== $mandate && false === $mandate->getJobs()->contains($this)) {
            $mandate->addJob($this);
        }

        $this->mandate = $mandate;

        return $this;
    }

    /**
     * @return Mandate|null
     */
    public function getMandate()
    {
        return $this->mandate;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addUser(User $user)
    {
        // Check for duplication
        if (false === $this->users->contains($user)) {
            $this->users->add($user);
        }

        // Ensure the relation is bidirectional
        if (false === $user->getJobs()->contains($this)) {
            $user->addJob($this);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);

        // Ensure the relation is unset for both entities
        // The check must be done to avoid circular references
        if (true === $user->getJobs()->contains($this)) {
            $user->removeJob($this);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }
}
