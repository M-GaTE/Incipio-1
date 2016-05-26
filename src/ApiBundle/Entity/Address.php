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

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Address
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Complementary data of the “delivery point”: stairs, apartment, floor, at somebody’s house, etc.
     *
     * @var string
     *
     * @ORM\Column(name="complementary_home", type="string", nullable=true)
     */
    private $complementaryHome;

    /**
     * Complementary data on the location: building, residence, etc.
     *
     * @var string
     *
     * @ORM\Column(name="complementry_location", type="string", nullable=true)
     */
    private $complementaryLocation;

    /**
     * Street number + bis, ter... + kind of street + street name.
     *
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * Distribution service, complementary data on the street location (BP, lieu-dit...).
     *
     * @var string
     *
     * @ORM\Column(name="complementary_street", type="string", length=255, nullable=true)
     */
    private $complementaryStreet;

    /**
     * Postal or Cedex code.
     *
     * @var string
     *
     * @ORM\Column(name="postal", type="string", length=20, nullable=true)
     */
    private $postal;

    /**
     * City name.
     *
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * Country name.
     *
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set complementaryHome.
     *
     * @param string $complementaryHome
     *
     * @return Address
     */
    public function setComplementaryHome($complementaryHome)
    {
        $this->complementaryHome = $complementaryHome;

        return $this;
    }

    /**
     * Get complementaryHome.
     *
     * @return string|null
     */
    public function getComplementaryHome()
    {
        return $this->complementaryHome;
    }

    /**
     * @return $this
     */
    public function resetComplementaryHome()
    {
        $this->complementaryHome = null;

        return $this;
    }

    /**
     * Set complementaryLocation.
     *
     * @param string $complementaryLocation
     *
     * @return Address
     */
    public function setComplementaryLocation($complementaryLocation)
    {
        $this->complementaryLocation = $complementaryLocation;

        return $this;
    }

    /**
     * Get complementaryLocation.
     *
     * @return string|null
     */
    public function getComplementaryLocation()
    {
        return $this->complementaryLocation;
    }

    /**
     * @return $this
     */
    public function resetComplementaryLocation()
    {
        $this->complementaryLocation = null;

        return $this;
    }

    /**
     * Set street.
     *
     * @param string $street
     *
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street.
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set complementaryStreet.
     *
     * @param string $complementaryStreet
     *
     * @return Address
     */
    public function setComplementaryStreet($complementaryStreet)
    {
        $this->complementaryStreet = $complementaryStreet;

        return $this;
    }

    /**
     * Get complementaryStreet.
     *
     * @return string|null
     */
    public function getComplementaryStreet()
    {
        return $this->complementaryStreet;
    }

    /**
     * @return $this
     */
    public function resetComplementaryStreet()
    {
        $this->complementaryStreet = null;

        return $this;
    }

    /**
     * Set postal.
     *
     * @param int $postal
     *
     * @return Address
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;

        return $this;
    }

    /**
     * Get postal.
     *
     * @return int|null
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
