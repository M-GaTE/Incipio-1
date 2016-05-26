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
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class User: user that have an account in the application.
 *
 * In this class, the term "organization" refers either to a Junior-Entreprise, Creation and such or a company.
 *
 * @ORM\Entity
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class User extends BaseUser
{
    const TYPE_CONTRACTOR = 'TYPE_CONTRACTOR';
    const TYPE_MEMBER = 'TYPE_MEMBER';

    /**
     * {@inheritdoc}
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    private $address;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"user-read"})
     */
    protected $createdAt;

    /**
     * {@inheritdoc}
     *
     * @Iri("https://schema.org/email")
     * @Assert\Email
     * @Assert\NotBlank
     * @Groups({"user"})
     */
    protected $email;

    /**
     * {@inheritdoc}
     *
     * @Assert\NotNull
     * @Groups({"user"})
     *
     * @TODO: validation for boolean
     */
    protected $enabled = false;

    /**
     * @var int
     *
     * @Iri("https://schema.org/name")
     * @ORM\Column(type="smallint", nullable=true)
     * @Assert\Range(
     *      min=1900,
     *      max=2100,
     *      minMessage="The ending school year must be a valid year.",
     *      maxMessage="The ending school year must be a valid year.",
     *      invalidMessage="The ending school year must be a valid year."
     * )
     * @Groups({"user"})
     */
    private $endingSchoolYear;

    /**
     * @var string
     *
     * @Iri("https://schema.org/name")
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Groups({"user"})
     *
     * @TODO: validation for username!
     */
    protected $fullname;

    /**
     * @var string Professional email.
     *
     * @Iri("https://schema.org/email")
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email
     * @Groups({"user"})
     */
    private $organizationEmail;

    /**
     * @var string Professional email in lowercase for search and string comparison; cf emailCanonical & passwordCanonical
     *
     * @Iri("https://schema.org/email")
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email
     * @Groups({"user-write"})
     */
    private $organizationEmailCanonical;

    private $phones;

    /**
     * {@inheritdoc}
     *
     * @var string
     *
     * @Groups({"user-write"})
     * @TODO: validation for the password!
     */
    protected $plainPassword;

    /**
     * {@inheritdoc}
     *
     * @Groups({"user"})
     */
    protected $roles;

    /**
     * Validated via {@see ::validate()}.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     * @Groups({"user"})
     */
    private $types = [];

    /**
     * @var StudentConvention
     *
     * @ORM\OneToOne(targetEntity="StudentConvention")
     * @ORM\JoinColumn(referencedColumnName="reference")
     * @Groups({"user"})
     */
    private $studentConvention;

    /**
     * {@inheritdoc}
     *
     * @Assert\Type("string")
     * @Groups({"user"})
     *
     * @TODO: validation for username!
     */
    protected $username;

    /**
     * @var ArrayCollection|Job[] List of job for this user.
     *
     * @ORM\ManyToMany(targetEntity="Job", mappedBy="users")
     * @Groups({"user"})
     *
     * @TODO: validation: may have no user
     **/
    protected $jobs;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @Groups({"user-read"})
     */
    protected $updatedAt;

    public function __construct()
    {
        parent::__construct();

        $this->jobs = new ArrayCollection();
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $endingSchoolYear
     *
     * @return $this
     */
    public function setEndingSchoolYear($endingSchoolYear)
    {
        $this->endingSchoolYear = $endingSchoolYear;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEndingSchoolYear()
    {
        return $this->endingSchoolYear;
    }

    /**
     * @param string|null $fullname
     *
     * @return $this
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Adds Job. Will automatically update job's user too.
     *
     * @param Job $job
     *
     * @return $this
     */
    public function addJob(Job $job)
    {
        // Check for duplicate
        if (false === $this->jobs->contains($job)) {
            $this->jobs->add($job);
        }

        // Ensure the relation is set for both entities
        if (false === $job->getUsers()->contains($this)) {
            $job->addUser($this);
        }

        return $this;
    }

    /**
     * Removes job. Will automatically update job's user too.
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
        if (true === $job->getUsers()->contains($this)) {
            $job->removeUser($this);
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
     * @param string $organizationEmail
     *
     * @return $this
     */
    public function setOrganizationEmail($organizationEmail)
    {
        $this->organizationEmail = $organizationEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrganizationEmail()
    {
        return $this->organizationEmail;
    }

    /**
     * @param string $organizationEmailCanonical
     *
     * @return $this
     */
    public function setOrganizationEmailCanonical($organizationEmailCanonical)
    {
        $this->organizationEmailCanonical = $organizationEmailCanonical;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrganizationEmailCanonical()
    {
        return $this->organizationEmailCanonical;
    }

    /**
     * @param StudentConvention|null $studentConvention
     *
     * @return $this
     */
    public function setStudentConvention(StudentConvention $studentConvention = null)
    {
        $this->studentConvention = $studentConvention;

        return $this;
    }

    /**
     * @return StudentConvention|null
     */
    public function getStudentConvention()
    {
        return $this->studentConvention;
    }

    /**
     * Add the given type if is not already present.
     *
     * @param string $type See ::getAllowedTypes() for valid values
     *
     * @return $this
     */
    public function addType($type)
    {
        if (!in_array($type, $this->types)) {
            $this->types[] = $type;
        }

        return $this;
    }

    /**
     * See ::getAllowedTypes() for valid values.
     *
     * @param array $types
     *
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return array Array of all valid values for the ::type property.
     */
    public static function getAllowedTypes()
    {
        return [
            'contractor' => self::TYPE_CONTRACTOR,
            'member' => self::TYPE_MEMBER,
        ];
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        $allowedTypes = array_flip($this->getAllowedTypes());

        foreach ($this->getTypes() as $type) {
            if (!isset($allowedTypes[$type])) {
                $context
                    ->buildViolation('This type is not a valid type')
                    ->atPath('types')
                    ->addViolation();
            }
        }
    }
}
