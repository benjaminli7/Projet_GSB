<?php

namespace App\Controller;

use App\Form\IdentificationVisiteurType;
use App\Entity\Employe;
use App\Entity\Formation;
use App\Entity\Visiteur;
use App\Entity\Inscription;
use App\Form\FormAjouterFormationType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
 

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class VisiteurController extends AbstractController
{
    /**
     * @Route("/visiteur", name="visiteur")
     */
    public function index()
    {
        return $this->render('visiteur/index.html.twig', [
            'controller_name' => 'VisiteurController',
        ]);
    }

    /**
     * @Route("/identificationVisiteur", name="app_identificationVisiteur")
     * Fonction permettant de s'identifier côté visiteur
     */

    public function identifiant(Request $request) 
    {
        $getListVisiteur = $this->getDoctrine()->getRepository(Visiteur::class);
        $form = $this->createForm(IdentificationVisiteurType::class);
        $form->handleRequest($request);
        $typeUser = "Visiteur";
        $msg = null;
        if($form->isSubmitted() && $form->isValid())
        {
            $getValue = $form->getData();

            $condition3 = $getListVisiteur->findOneBy(['login' => $getValue->getLogin()]);
            $condition4 = $getListVisiteur->findOneBy(['mdp' => $getValue->getMdp()]);

            if($condition3 == null || $condition4 == null){
                $msg = "Login ou mot de passe incorrect !";
                return $this->render('gsb/gestion_formations/connexion.html.twig', array('form'=>$form->createView(), 'msg' => $msg, 'typeUser' => $typeUser));;
            }

            if($condition3->getId() == $condition4->getId()){
                $id = $condition3->getId();
                $session = new Session();
                $session->set("id",$id);
                $session->set("typeUser", "visiteur");
                $session->get($id);
                $session->get("visiteur");
                $session->start();
               
                return $this->redirectToRoute('aff_formation_visiteur');
            }
        }
        return $this->render('gsb/gestion_formations/connexion.html.twig', array('form'=>$form->createView(), 'msg' => $msg, 'typeUser' => $typeUser));

    }

    /**
     * @Route("/affListeFormationsVisiteur/", name="aff_formation_visiteur")
     * Fonction permettant d'afficher la liste des formations auxquelles le visiteur peut s'inscrire
     */
    public function afficheLesFormations()
    {
        $session = $this->container->get('session')->get('id');
        $typeUser = $this->container->get('session')->get('typeUser');
        if($session == null){
            return $this->redirectToRoute('app_identification'); 
        }
        $formation = $this->getDoctrine()->getRepository(Formation::class)->findAll();
        if(!$formation){
            $message="Pas de formation";
        }
        else{
            $message = null;
        }
        return $this->render('gsb/inscription_formations/listeFormationVisiteur.html.twig', array('lesFormations'=>$formation,'message'=>$message, 'typeUser'=>$typeUser, 'inscriptionMsg'=>null));

    }

    /**
     * @Route("/bienvenue", name="app_bienvenue")
     * Fonction affichant la page de bienvenue qui permet de choisir de se connecter en tant qu'employé ou visiteur
     */
    public function bienvenue(){
        return $this->render('gsb/gestion_formations/bienvenue.html.twig'); 
    }

    /**
     * @Route("/inscriptionVisiteur/{id}", name="app_inscription_visiteur")
     * Fonction permettant de s'inscrire à une formation 
     */
    public function inscriptionVisiteur($id, ObjectManager $manager){
        $session = $this->container->get('session')->get('id');
        $typeUser = $this->container->get('session')->get('typeUser');
        $inscriptionMsg = null;
        $message = null;
        $visiteur = $this->getDoctrine()->getRepository(Visiteur::class)->find($session);
        $formation = $this->getDoctrine()->getRepository(Formation::class)->find($id);
        // $lstInscription = $this->getDoctrine()->getRepository(Inscription::class)->findAll();
        $lstInscription = $this->getDoctrine()->getRepository(Inscription::class);



  
        $condition=$lstInscription->findOneBy(['formation' => $formation]);


        if($condition){
            $message = "Vous vous êtes déjà inscrit à cette formation";
        }
        else{
            $inscription = new Inscription();
            $inscription->setStatut("E");
            $inscription->setFormation($formation);
            $inscription->setVisiteur($visiteur);
            $manager->persist($inscription);
            $manager->flush();
            $inscriptionMsg = "Vous vous êtes bien inscrits à cette formation !";    
        }
        return $this->render('gsb/inscription_formations/listeFormationVisiteur.html.twig', array('lesFormations'=>$formation,'message'=>$message, 'typeUser'=>$typeUser, 'inscriptionMsg'=>$inscriptionMsg));
    }
}
