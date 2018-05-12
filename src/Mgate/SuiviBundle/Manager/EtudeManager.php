<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Manager;

use Doctrine\ORM\EntityManager;
use Mgate\SuiviBundle\Entity\Etude as Etude;
use Mgate\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class EtudeManager
{
    protected $em;

    protected $authorizationChecker;

    protected $tva;

    protected $namingConvention;

    protected $anneeCreation;

    protected $defaultFraisDossier;

    protected $defaultPourcentageAcompte;

    public function __construct(EntityManager $em, KeyValueStore $keyValueStore, AuthorizationChecker $authorizationChecker)
    {
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
        if ($keyValueStore->exists('tva')) {
            $this->tva = $keyValueStore->get('tva');
        } else {
            throw new \LogicException('Parameter TVA is undefined.');
        }

        if ($keyValueStore->exists('namingConvention')) {
            $this->namingConvention = $keyValueStore->get('namingConvention');
        } else {
            $this->namingConvention = 'id';
        }

        if ($keyValueStore->exists('anneeCreation')) {
            $this->anneeCreation = intval($keyValueStore->get('anneeCreation'));
        } else {
            throw new \LogicException('Parameter Année Creation is undefined.');
        }

        if ($keyValueStore->exists('fraisDossierDefaut')) {
            $this->defaultFraisDossier = $keyValueStore->get('fraisDossierDefaut');
        } else {
            throw new \LogicException('Parameter Frais Dossier Defaut is undefined.');
        }

        if ($keyValueStore->exists('pourcentageAcompteDefaut')) {
            $this->defaultPourcentageAcompte = $keyValueStore->get('pourcentageAcompteDefaut');
        } else {
            throw new \LogicException('Parameter Pourcentage Acompte Defaut is undefined.');
        }
    }

    /**
     * @param Etude $etude
     * @param User  $user
     *
     * @return bool
     *              Comme l'authorizationChecker n'est pas dispo coté twig, on utilisera cette méthode uniquement dans les controllers.
     *              Pour twig, utiliser confidentielRefusTwig(Etude, User, is_granted('ROLE_SOUHAITE'))
     */
    public function confidentielRefus(Etude $etude, User $user)
    {
        try {
            if ($etude->getConfidentiel() && !$this->authorizationChecker->isGranted('ROLE_CA')) {
                if ($etude->getSuiveur() && $user->getPersonne()->getId() != $etude->getSuiveur()->getId()) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return true;
        }

        return false;
    }

    /**
     * Get référence du document
     * Params : Etude $etude, mixed $doc, string $type (the type of doc).
     *
     * @param Etude $etude
     * @param $type
     * @param int $key
     *
     * @return string
     */
    public function getRefDoc(Etude $etude, $type, $key = -1)
    {
        $type = strtoupper($type);
        $name = $etude->getReference($this->namingConvention);
        if ('AP' == $type) {
            if ($etude->getAp()) {
                return $name . '-' . $type . '-' . $etude->getAp()->getVersion();
            } else {
                return $name . '-' . $type . '- ERROR GETTING VERSION';
            }
        } elseif ('CC' == $type) {
            if ($etude->getCc()) {
                return $name . '-' . $type . '-' . $etude->getCc()->getVersion();
            } else {
                return $name . '-' . $type . '- ERROR GETTING VERSION';
            }
        } elseif ('RM' == $type || 'DM' == $type) {
            if ($key < 0) {
                return $name . '-' . $type;
            }
            if (!$etude->getMissions()->get($key)
                || !$etude->getMissions()->get($key)->getIntervenant()
            ) {
                return $name . '-' . $type . '- ERROR GETTING DEV ID - ERROR GETTING VERSION';
            } else {
                return $name . '-' . $type . '-' . $etude->getMissions()->get($key)->getIntervenant()->getIdentifiant() . '-' . $etude->getMissions()->get($key)->getVersion();
            }
        } elseif ('FA' == $type) {
            return $name . '-' . $type;
        } elseif ('FI' == $type) {
            return $name . '-' . $type . ($key + 1);
        } elseif ('FS' == $type) {
            return $name . '-' . $type;
        } elseif ('PVI' == $type) {
            if ($key >= 0 && $etude->getPvis($key)) {
                return $name . '-' . $type . ($key + 1) . '-' . $etude->getPvis($key)->getVersion();
            } else {
                return $name . '-' . $type . ($key + 1) . '- ERROR GETTING PVI';
            }
        } elseif ('PVR' == $type) {
            if ($etude->getPvr()) {
                return $name . '-' . $type . '-' . $etude->getPvr()->getVersion();
            } else {
                return $name . '-' . $type . '- ERROR GETTING VERSION';
            }
        } elseif ('CE' == $type) {
            if (!$etude->getMissions()->get($key)
                || !$etude->getMissions()->get($key)->getIntervenant()
            ) {
                return $etude->getMandat() . '-CE- ERROR GETTING DEV ID';
            } else {
                $identifiant = $etude->getMissions()->get($key)->getIntervenant()->getIdentifiant();
            }

            return $etude->getMandat() . '-CE-' . $identifiant;
        } elseif ('AVCC' == $type) {
            if ($etude->getCc() && $etude->getAvs()->get($key)) {
                return $name . '-CC-' . $etude->getCc()->getVersion() . '-AV' . ($key + 1) . '-' . $etude->getAvs()->get($key)->getVersion();
            } else {
                return $name . '-' . $type . '- ERROR GETTING VERSION';
            }
        } else {
            return 'ERROR';
        }
    }

    /**
     * Get nouveau numéro d'etude, pour valeur par defaut dans formulaire.
     */
    public function getNouveauNumero()
    {
        $mandat = $this->getMaxMandat();
        $qb = $this->em->createQueryBuilder();

        $query = $qb->select('e.num')
            ->from('MgateSuiviBundle:Etude', 'e')
            ->andWhere('e.mandat = :mandat')
            ->setParameter('mandat', $mandat)
            ->orderBy('e.num', 'DESC');

        $value = $query->getQuery()->setMaxResults(1)->getOneOrNullResult();
        if ($value) {
            return $value['num'] + 1;
        } else {
            return 1;
        }
    }

    /**
     * Get frais de dossier par défaut.
     */
    public function getDefaultFraisDossier()
    {
        return $this->defaultFraisDossier;
    }

    /**
     * Get pourcentage d'acompte par défaut.
     */
    public function getDefaultPourcentageAcompte()
    {
        return $this->defaultPourcentageAcompte;
    }

    /**
     * Converti le numero de mandat en année.
     *
     * @param $idMandat
     *
     * @return string
     */
    public function mandatToString($idMandat)
    {
        return strval($this->anneeCreation + $idMandat) . '/' . strval($this->anneeCreation + 1 + $idMandat);
    }

    /**
     * Get le maximum des mandats.
     */
    public function getMaxMandat()
    {
        $qb = $this->em->createQueryBuilder();

        $query = $qb->select('e.mandat')
            ->from('MgateSuiviBundle:Etude', 'e')
            ->orderBy('e.mandat', 'DESC');

        $value = $query->getQuery()->setMaxResults(1)->getOneOrNullResult();
        if ($value) {
            return $value['mandat'];
        } else {
            return 0;
        }
    }

    /**
     * Get le minimum des mandats.
     */
    public function getMinMandat()
    {
        $qb = $this->em->createQueryBuilder();

        $query = $qb->select('e.mandat')
            ->from('MgateSuiviBundle:Etude', 'e')
            ->orderBy('e.mandat', 'ASC');

        $value = $query->getQuery()->setMaxResults(1)->getOneOrNullResult();
        if ($value) {
            return $value['mandat'];
        } else {
            return 0;
        }
    }

    /**
     * Get le maximum des mandats par rapport à la date de Signature de signature des CC.
     */
    public function getMaxMandatCc()
    {
        $qb = $this->em->createQueryBuilder();

        $query = $qb->select('c.dateSignature')
            ->from('MgateSuiviBundle:Cc', 'c')
            ->orderBy('c.dateSignature', 'DESC');

        $value = $query->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if ($value) {
            return $this->dateToMandat($value['dateSignature']);
        } else {
            return 0;
        }
    }

    /**
     * Converti le numero de mandat en année.
     *
     * @param \DateTime $date
     *
     * @return int
     */
    public function dateToMandat(\DateTime $date)
    {
        $interval = new \DateInterval('P2M20D');
        $date2 = clone $date;
        $date2->sub($interval);

        return intval($date2->format('Y')) - $this->anneeCreation;
    }

    /**
     * Taux de conversion.
     */
    public function getTauxConversion()
    {
        $tauxConversion = [];

        //recup toute les etudes
        $etudes = $this->em->getRepository('MgateSuiviBundle:Etude')->findAll();
        foreach ($etudes as $etude) {
            $mandat = $etude->getMandat();
            if (null !== $etude->getAp()) {
                if ($etude->getAp()->getSpt2()) {
                    if (isset($tauxConversion[$mandat])) {
                        $ApRedige = $tauxConversion[$mandat]['ap_redige'];
                        ++$ApRedige;
                        $ApSigne = $tauxConversion[$mandat]['ap_signe'];
                        ++$ApSigne;
                    } else {
                        $ApRedige = 1;
                        $ApSigne = 1;
                    }
                    $tauxConversionCalc = ['mandat' => $mandat, 'ap_redige' => $ApRedige, 'ap_signe' => $ApSigne];
                    $tauxConversion[$mandat] = $tauxConversionCalc;
                } elseif ($etude->getAp()->getRedige()) {
                    if (isset($tauxConversion[$mandat])) {
                        $ApRedige = $tauxConversion[$mandat]['ap_redige'];
                        ++$ApRedige;
                        $ApSigne = $tauxConversion[$mandat]['ap_signe'];
                    } else {
                        $ApRedige = 1;
                        $ApSigne = 0;
                    }
                    $tauxConversionCalc = ['mandat' => $mandat, 'ap_redige' => $ApRedige, 'ap_signe' => $ApSigne];
                    $tauxConversion[$mandat] = $tauxConversionCalc;
                }
            }
        }

        return $tauxConversion;
    }
}
