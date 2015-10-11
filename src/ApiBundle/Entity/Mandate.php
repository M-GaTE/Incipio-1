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
 * @ORM\EntityListeners({"ApiBundle\EventListener\Doctrine\MandateListener"})
 * @UniqueEntity("name")
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Mandate
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
     * @var \DateTime
     *
     * @Iri("https://schema.org/endDate")
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     * @Assert\NotNull
     * @Groups({"job", "user"})
     */
    private $endAt;

    /**
     * @var ArrayCollection|Job[] List of jobs attached to this mandate.
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="mandate", cascade={"all"})
     */
    private $jobs;

    /**
     * @var string
     *
     * @Iri("https://schema.org/name")
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\Length(min=5, max=30)
     * @Groups({"job", "user"})
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @Iri("https://schema.org/startDate")
     * @ORM\Column(type="datetime")
     * @Assert\Date
     * @Assert\NotNull
     * @Groups({"job", "user"})
     */
    private $startAt;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime|null $endAt
     *
     * @return $this
     */
    public function setEndAt(\DateTime $endAt = null)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param Job[] $jobs
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If jobs type is incorrect
     */
    public function setJobs($jobs)
    {
        $this->jobs = new ArrayCollection();
        foreach ($jobs as $job) {
            $this->addJob($job);
        }

        return $this;
    }

    /**
     * Adds Job. Will automatically update job's mandate too.
     *
     * @param Job $job
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If job type is incorrect
     */
    public function addJob($job)
    {
        if (false === $job instanceof Job) {
            throw new \InvalidArgumentException('Unexpected type.');
        }

        // Check for duplication
        if (false === $this->jobs->contains($job)) {
            $this->jobs->add($job);
            $this->jobs = new ArrayCollection(array_values($this->jobs->toArray()));
        }

        // Ensure the relation is bidirectional
        $job->setMandate($this);

        return $this;
    }

    /**
     * Removes job. Will automatically update job's mandate too.
     *
     * @param Job $job
     *
     * @return $this
     */
    public function removeJob(Job $job)
    {
        $this->jobs->removeElement($job);

        // Ensure the relation is unset for both entities
        // The check must be done to avoid circular references
        if (null !== $job->getMandate()) {
            $job->setMandate(null);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \DateTime $startAt
     *
     * @return $this
     */
    public function setStartAt(\DateTime $startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartAt()
    {
        return $this->startAt;
    }
}
