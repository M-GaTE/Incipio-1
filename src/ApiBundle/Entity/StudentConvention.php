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

use Doctrine\ORM\Mapping as ORM;
use Dunglas\ApiBundle\Annotation\Iri;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Convention signed between a member of the organization and the organization. Is a weak entity of {@see
 * ApiBundle\Entity\User}.
 *
 * @ORM\Entity
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConvention
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\Length(min=5, max=30)
     * @ORM\GeneratedValue(strategy="NONE")
     * @Groups({"user"})
     * TODO: remove the group as the reference is the ID it should be unneeded; See ticket on DunglasApiBundle for solving this bug
     *
     * @link https://github.com/dunglas/DunglasApiBundle/issues/187
     */
    private $reference;

    /**
     * @var \DateTime Date at which the convention has been signed.
     *
     * @Iri("https://schema.org/startDate")
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     * @Groups({"user"})
     */
    private $dateOfSignature;

    /**
     * @return string|null
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateOfSignature()
    {
        return $this->dateOfSignature;
    }

    /**
     * @param \DateTime|null $dateOfSignature
     *
     * @return $this
     */
    public function setDateOfSignature(\DateTime $dateOfSignature = null)
    {
        $this->dateOfSignature = $dateOfSignature;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSigned()
    {
        return $this->dateOfSignature instanceof \DateTime;
    }
}
