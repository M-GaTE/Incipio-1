<?php

namespace mgate\SuiviBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Form\EtudeType;
use mgate\SuiviBundle\Form\EtudeHandler;
use mgate\SuiviBundle\Form\EtudePhasesType;
use mgate\SuiviBundle\Form\EtudePhasesHandler;
use mgate\SuiviBundle\Entity\Ap;
use mgate\SuiviBundle\Form\ApType;
use mgate\SuiviBundle\Form\ApHandler;
use mgate\SuiviBundle\Entity\Cc;
use mgate\SuiviBundle\Form\CcType;
use mgate\SuiviBundle\Form\CcHandler;
use mgate\SuiviBundle\Entity\Mission;
use mgate\SuiviBundle\Form\MissionType;
use mgate\SuiviBundle\Form\MissionHandler;
use mgate\SuiviBundle\Entity\Suivi;
use mgate\SuiviBundle\Form\SuiviType;
use mgate\SuiviBundle\Form\SuiviHandler;
use mgate\SuiviBundle\Entity\ClientContact;
use mgate\SuiviBundle\Form\ClientContactHandler;
use mgate\SuiviBundle\Form\ClientContactType;
use mgate\SuiviBundle\Entity\Pvi;
use mgate\SuiviBundle\Form\PviHandler;
use mgate\SuiviBundle\Form\PviType;
use mgate\SuiviBundle\Entity\Av;
use mgate\SuiviBundle\Form\AvHandler;
use mgate\SuiviBundle\Form\AvType;
use mgate\SuiviBundle\Entity\AvMission;
use mgate\SuiviBundle\Form\AvMissionHandler;
use mgate\SuiviBundle\Form\AvMissionType;
use mgate\SuiviBundle\Entity\Facture;
use mgate\SuiviBundle\Form\FactureHandler;
use mgate\SuiviBundle\Form\FactureType;
use mgate\SuiviBundle\Entity\Pvr;
use mgate\SuiviBundle\Form\PvrHandler;
use mgate\SuiviBundle\Form\PvrType;

class EtudeController extends Controller
{
    
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgateSuiviBundle:Etude')->findAll();

        return $this->render('mgateSuiviBundle:Etude:index.html.twig', array(
            'etudes' => $entities,
        ));
         
    }
    
    public function addAction()
    {
        $etude = new Etude;
        
        $form        = $this->createForm(new EtudeType, $etude);
        $formHandler = new EtudeHandler($form, $this->get('request'), $this->getDoctrine()->getEntityManager());
        echo 'caca';
        
        if($formHandler->process())
        {
            var_dump($this->get('request')->request->get('envoie'));
           
            if($this->get('request')->get('ap'))
            {
                
                return $this->redirect($this->generateUrl('mgateSuivi_ap_ajouter', array('id' => $etude->getId())));
            }
            else
            {
                return $this->redirect($this->generateUrl('mgateSuivi_etude_voir', array('id' => $etude->getId())));
            }

        }

        return $this->render('mgateSuiviBundle:Etude:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
        
    }
    
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('mgateSuiviBundle:Etude')->find($id); // Ligne qui posse problème

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Etude entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('mgateSuiviBundle:Etude:voir.html.twig', array(
            'etude'      => $entity,
            /*'delete_form' => $deleteForm->createView(),  */      ));
        
    }
    
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        // On vérifie que l'article d'id $id existe bien, sinon, erreur 404.
        if( ! $etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id) )
        {
            throw $this->createNotFoundException('Article[id='.$id.'] inexistant');
        }

        // On passe l'$article récupéré au formulaire
        $form        = $this->createForm(new EtudeType, $etude);
        $formHandler = new EtudeHandler($form, $this->get('request'), $em);

        if($formHandler->process())
        {
            return $this->redirect( $this->generateUrl('mgateSuivi_etude_voir', array('id' => $etude->getId())) );
        }

        return $this->render('mgateSuiviBundle:Etude:modifier.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
    
    public function modifierPhasesAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        if( ! $etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id) )
        {
            throw $this->createNotFoundException('Etude[id='.$id.'] inexistant');
        }

        $form        = $this->createForm(new EtudePhasesType, $etude);
        $formHandler = new EtudePhasesHandler($form, $this->get('request'), $em);

        if($formHandler->process())
        {
            return $this->redirect( $this->generateUrl('mgateSuivi_etude_voir', array('id' => $etude->getId())) );
        }

        return $this->render('mgateSuiviBundle:Etude:phases.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
}
