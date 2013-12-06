<?php

namespace mgate\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Ob\HighchartsBundle\Highcharts\Highchart;
use mgate\SuiviBundle\Entity\EtudeRepository;

// A externaliser dans les parametres
define("STATE_ID_EN_COURS_X", 2);
define("STATE_ID_TERMINEE_X", 4);

class Indicateur {

    private $titre;
    private $methode;

    public function getTitre() {
        return $this->titre;
    }

    public function getMethode() {
        return $this->methode;
    }

    public function setTitre($x) {
        $this->titre = $x;
        return $this;
    }

    public function setMethode($x) {
        $this->methode = $x;
        return $this;
    }

}

class IndicateursCollection{
    private $indicateurs;
    private $autorizedMethods;
    
    function __construct() {
        $this->indicateurs = array();
        $this->autorizedMethods = array();
    }

    public function addCategorieIndicateurs($categorie){
        if(!array_key_exists($categorie, $this->indicateurs))
            $this->indicateurs[$categorie] = array();
        return $this;
    }
    
    public function setIndicateurs(Indicateur $indicateur, $categorie) {
        $this->indicateurs[$categorie][] = $indicateur;
        $this->setAutorizedMethods($indicateur->getMethode());
        return $this;
    }

    public function getIndicateurs($categorie = NULL) {
        if ($categorie !== NULL)
            return $this->indicateurs[$categorie];
        else
            return $categorie;
    }

    public function getAutorizedMethods() {
        return $this->autorizedMethods;
    }

    public function setAutorizedMethods($method) {
        if(is_string($method))
            array_push($this->autorizedMethods, $method);
        else
            $this->autorizedMethods = $method;
        return $this;
    }

}

class IndicateursController extends Controller {
    
    public $indicateursCollection;
    
    function __construct() {
        $this->indicateursCollection = new IndicateursCollection();
        if(isset($_SESSION['autorizedMethods']))
            $this->indicateursCollection->setAutorizedMethods($_SESSION['autorizedMethods']);
    }
    
    /**
     * @Secure(roles="ROLE_CA")
     */
    public function indexAction() {
        if(isset($_SESSION['autorizedMethods']))
            unset($_SESSION['autorizedMethods']);
        
        // Définition des catégories     
        $this->indicateursCollection
                ->addCategorieIndicateurs('Suivi')
                ->addCategorieIndicateurs('Rfp')
                ->addCategorieIndicateurs('Com')
                ->addCategorieIndicateurs('Treso')
                ->addCategorieIndicateurs('Gestion');
        
        /************************************************
         * 			Indicateurs Suivi d'études			*
         ************************************************/
        //Chiffre d'affaires en fonction du temps sur les Mandats
        $chiffreAffaires = new Indicateur();
        $chiffreAffaires->setTitre('Evolution du Chiffre d\'Affaires')
                ->setMethode('getCA');
        

        //??
        $ressourcesHumaines = new Indicateur();
        $ressourcesHumaines->setTitre('Evolution RH')
                ->setMethode('getRh');
				
		//Chiffre d'affaires par mandat
		$chiffreAffairesMandat = new Indicateur();
        $chiffreAffairesMandat->setTitre('Evolution du Chiffre d\'Affaires par Mandat')
                ->setMethode('getCAM');
				
		//Ration nbre JEH/nbre études
		$ratioJEHetudes = new Indicateur();
        $ratioJEHetudes->setTitre('Ratio nombre de JEH/nombre d\'études par Mandat')
                ->setMethode('getRATIOJEH');
        
        
        $this->indicateursCollection
                ->setIndicateurs($chiffreAffaires, 'Suivi')
                //->setIndicateurs($ressourcesHumaines, 'Suivi')
				->setIndicateurs($chiffreAffairesMandat, 'Suivi')
				->setIndicateurs($ratioJEHetudes, 'Suivi');
        /************************************************
         * 			Indicateurs Gestion Asso			*
         ************************************************/
        /************************************************
         * 				Indicateurs RFP					*
         ************************************************/
        /************************************************
         * 			Indicateurs Trésorerie 			*
         ************************************************/
        /************************************************
         * 		Indicateurs Prospection Commerciale		*
         ************************************************/


        //Enregistrement Cross Requete des Méthodes tolérées
        $_SESSION['autorizedMethods'] = $this->indicateursCollection->getAutorizedMethods();
        
        return $this->render('mgateStatBundle:Indicateurs:index.html.twig', 
                array('indicateursSuivi' => $this->indicateursCollection->getIndicateurs('Suivi'),
                    'indicateursRfp' => $this->indicateursCollection->getIndicateurs('Rfp'),
                    'indicateursGestion' => $this->indicateursCollection->getIndicateurs('Gestion'),
                    'indicateursCom' => $this->indicateursCollection->getIndicateurs('Com'),
                    'indicateursTreso' => $this->indicateursCollection->getIndicateurs('Treso'),
                ));
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function debugAction($get){
        $indicateur = new Indicateur();
        $indicateur->setTitre($get)
                ->setMethode($get);
        return $this->render('mgateStatBundle:Indicateurs:debug.html.twig', 
                array('indicateur' => $indicateur,
                    'chart'=> $get(),
                ));
    }

    /**
     * @Secure(roles="ROLE_CA")
     */
    public function ajaxAction() {
        $request = $this->get('request');

        if ($request->getMethod() == 'GET') {
            $chartMethode = $request->query->get('chartMethode');
            if (in_array($chartMethode, $this->indicateursCollection->getAutorizedMethods()))
                return $this->$chartMethode();
        }
        return new \Symfony\Component\HttpFoundation\Response('<!-- Chart '. $chartMethode .' does not exist. -->');
    }

    /**
     * @Secure(roles="ROLE_CA")
     */
    private function getCA() {
        $etudeManager = $this->get('mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();
        $etude = new \mgate\SuiviBundle\Entity\Etude;
        $Ccs = $this->getDoctrine()->getManager()->getRepository('mgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        //$data = array();
        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumuls[$i] = 0;

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == STATE_ID_EN_COURS_X
                    || $etude->getStateID() == STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etudeManager->getTotalHT($etude);

                $interval = new \DateInterval('P' . ($maxMandat - $idMandat) . 'Y');
                $dateDecale = clone $dateSignature;
                $dateDecale->add($interval);

                $mandats[$idMandat][]
                        = array("x" => $dateDecale->getTimestamp() * 1000,
                    "y" => $cumuls[$idMandat], "name" => $etudeManager->getRefEtude($etude) . " - " . $etude->getNom(),
                    'date' => $dateDecale->format('d/m/Y'),
                    'prix' => $etudeManager->getTotalHT($etude));
            }
        }



        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array("name" => "Mandat " . $idMandat . " - " . $etudeManager->mandatToString($idMandat), "data" => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);



        //WARN :::

        $ob->chart->renderTo('getCA');  // The #id of the div where to render the chart
        ///

        $ob->xAxis->labels(array('style' => $style));
        $ob->yAxis->labels(array('style' => $style));
        $ob->title->text('Évolution par mandat du chiffre d\'affaire signé cumulé');
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => "Date", 'style' => $style));
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(array('month' => "%b"));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Chiffre d'Affaire signé cumulé", 'style' => $style));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} le {point.date}<br />{point.name} à {point.prix} €');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(90);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(array('lineWidth' => 5, 'marker' => array('radius' => 8)));
        $ob->series($series);

        //return $this->render('mgateStatBundle:Default:ca.html.twig', array(
        return $this->render('mgateStatBundle:Indicateurs:Indicateur.html.twig', array(
                    'chart' => $ob
                ));
    }

    /**
     * @Secure(roles="ROLE_CA")
     */
    private function getRh() {
        $etudeManager = $this->get('mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();
        $etude = new \mgate\SuiviBundle\Entity\Etude;
        $missions = $this->getDoctrine()->getManager()->getRepository('mgateSuiviBundle:Mission')->findBy(array(), array('debutOm' => 'asc'));

        //$data = array();
        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumuls[$i] = 0;

        $mandats[1] = array();

        //Etape 1 remplir toutes les dates
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateDebut = $mission->getdebutOm();
            $dateFin = $mission->getfinOm();

            if ($dateDebut && $dateFin) {
                $idMandat = $etudeManager->dateToMandat($dateDebut);

                $cumuls[0]++;

                //$interval = new \DateInterval('P' . ($maxMandat - $idMandat) . 'Y');
                $dateDebutDecale = clone $dateDebut;
                //$dateDebutDecale->add($interval);
                $dateFinDecale = clone $dateFin;
                //$dateFinDecale->add($interval);

                $addDebut = true;
                $addFin = true;
                foreach ($mandats[1] as $datePoint) {
                    if (($dateDebutDecale->getTimestamp() * 1000) == $datePoint['x'])
                        $addDebut = false;
                    if (($dateFinDecale->getTimestamp() * 1000) == $datePoint['x'])
                        $addFin = false;
                }

                if ($addDebut) {
                    $mandats[1][]
                            = array("x" => $dateDebutDecale->getTimestamp() * 1000,
                        "y" => 0/* $cumuls[0] */, "name" => $etudeManager->getRefEtude($etude) . " + " . $etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etudeManager->getTotalHT($etude));
                }
                if ($addFin) {
                    $mandats[1][]
                            = array("x" => $dateFinDecale->getTimestamp() * 1000,
                        "y" => 0/* $cumuls[0] */, "name" => $etudeManager->getRefEtude($etude) . " - " . $etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etudeManager->getTotalHT($etude));
                }
            }
        }

        //Etapes 2 trie dans l'ordre
        $callback = function($a, $b) use($mandats) {
                    return $mandats[1][$a]['x'] > $mandats[1][$b]['x'];
                };
        uksort($mandats[1], $callback);
        foreach ($mandats[1] as $entree)
            $mandats[2][] = $entree;
        $mandats[1] = array();

        //Etapes 3 ++ --
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateFin = $mission->getfinOm();
            $dateDebut = $mission->getdebutOm();

            if ($dateDebut && $dateFin) {
                $idMandat = $etudeManager->dateToMandat($dateFin);

                //$interval2 = new \DateInterval('P'.($maxMandat-$idMandat).'Y');
                $dateDebutDecale = clone $dateDebut;
                //$dateDebutDecale->add($interval2);
                $dateFinDecale = clone $dateFin;
                //$dateFinDecale->add($interval2);

                foreach ($mandats[2] as &$entree) {
                    if ($entree['x'] >= $dateDebutDecale->getTimestamp() * 1000 && $entree['x'] < $dateFinDecale->getTimestamp() * 1000) {
                        $entree['y']++;
                    }
                }
            }
        }

        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array("name" => "Mandat " . $idMandat . " - " . $etudeManager->mandatToString($idMandat), "data" => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);



        //WARN :::

        $ob->chart->renderTo('getRh');  // The #id of the div where to render the chart
        ///
        $ob->chart->type("spline");
        $ob->xAxis->labels(array('style' => $style));
        $ob->yAxis->labels(array('style' => $style));
        $ob->title->text("Évolution par mandat du nombre d'intervenant");
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => "Date", 'style' => $style));
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(array('month' => "%b"));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Nombre d'intervenant", 'style' => $style));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(90);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(array('lineWidth' => 5, 'marker' => array('radius' => 8)));
        $ob->series($series);

        //return $this->render('mgateStatBundle:Default:ca.html.twig', array(
        return $this->render('mgateStatBundle:Indicateurs:Indicateur.html.twig', array(
                    'chart' => $ob
                ));
    }
	
	/**
     * @Secure(roles="ROLE_CA")
     */
    private function getCAM() {
        $etudeManager = $this->get('mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();
        $etude = new \mgate\SuiviBundle\Entity\Etude;
        $Ccs = $this->getDoctrine()->getManager()->getRepository('mgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        //$data = array();
        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumuls[$i] = 0;

		$cumulsJEH = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumulsJEH[$i] = 0;
			
        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == STATE_ID_EN_COURS_X
                    || $etude->getStateID() == STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etudeManager->getTotalHT($etude);
				$cumulsJEH[$idMandat] += $etudeManager->getNbrJEH($etude);

                $mandats[$idMandat][]
                        = array("x" => $idMandat,
                    "y" => $cumuls[$idMandat], "name" => $etudeManager->getRefEtude($etude) . " - " . $etude->getNom(),
                    'prix' => $etudeManager->getTotalHT($etude),
					'JEH' => $cumulsJEH[$idMandat]);
            }
        }



        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array("name" => "Mandat " . $idMandat . " - " . $etudeManager->mandatToString($idMandat), "data" => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);



        //WARN :::

        $ob->chart->renderTo('getCAM');  // The #id of the div where to render the chart
        ///
		$ob->chart->type("column");
        $ob->xAxis->labels(array('style' => $style));
		$ob->xAxis->allowDecimals(false);
        $ob->yAxis->labels(array('style' => $style));
        $ob->title->text('Évolution du chiffre d\'affaire signé cumulé par mandat');
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => "Mandat", 'style' => $style));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Chiffre d'Affaire signé cumulé", 'style' => $style));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.JEH} JEH<br />{point.y} €');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(160);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->series($series);

        //return $this->render('mgateStatBundle:Default:ca.html.twig', array(
        return $this->render('mgateStatBundle:Indicateurs:Indicateur.html.twig', array(
                    'chart' => $ob
                ));
    }

	/**
     * @Secure(roles="ROLE_CA")
     */
    private function getRATIOJEH() {
        $etudeManager = $this->get('mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();
        $etude = new \mgate\SuiviBundle\Entity\Etude;
        $Ccs = $this->getDoctrine()->getManager()->getRepository('mgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        //$data = array();
        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumuls[$i] = 0;

		$cumulsJEH = array();
        for ($i = 0; $i <= $maxMandat; $i++)
            $cumulsJEH[$i] = 0;
			
        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == STATE_ID_EN_COURS_X
                    || $etude->getStateID() == STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat]++;
				$cumulsJEH[$idMandat] += $etudeManager->getNbrJEH($etude);

                $mandats[$idMandat][]
                        = array("x" => $idMandat,
                    "y" => $cumulsJEH[$idMandat] / $cumuls[$idMandat],
                    'etudes' => $cumuls[$idMandat],
					'JEH' => $cumulsJEH[$idMandat]);
            }
        }



        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array("name" => "Mandat " . $idMandat . " - " . $etudeManager->mandatToString($idMandat), "data" => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);



        //WARN :::

        $ob->chart->renderTo('getRATIOJEH');  // The #id of the div where to render the chart
        ///
		$ob->chart->type("column");
        $ob->xAxis->labels(array('style' => $style));
		$ob->xAxis->allowDecimals(false);
        $ob->yAxis->labels(array('style' => $style));
        $ob->title->text('Ratio nombre JEH/nombre d\'études par mandat');
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => "Mandat", 'style' => $style));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Ratio nombre JEH/nombre d'études", 'style' => $style));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.JEH} JEH<br />{point.etudes} études<br />ratio : {point.y}');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(160);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->series($series);

        //return $this->render('mgateStatBundle:Default:ca.html.twig', array(
        return $this->render('mgateStatBundle:Indicateurs:Indicateur.html.twig', array(
                    'chart' => $ob
                ));
    }
}