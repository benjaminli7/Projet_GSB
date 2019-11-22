<?php
 
namespace App\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\IdentificationFormType;
use App\Entity\Employe;
use App\Entity\Formation;
use App\Entity\Visiteur;
use App\Entity\Inscription;
use App\Form\FormAjouterFormationType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
 
class GsbController extends AbstractController
{
    /**
     * @Route("/gsb", name="gsb")
     */
    public function index()
    {
        return $this->render('gsb/index.html.twig', [
            'controller_name' => 'GsbController',
        ]);
    }
 
    /**
     * @Route("/identification", name="app_identification")
     * Fonction permettant de s'identifier côté employé
     */

    public function identifiant(Request $request)
    {
        $getListEmploye = $this->getDoctrine()->getRepository(Employe::class);
        $form = $this->createForm(IdentificationFormType::class);
        $form->handleRequest($request);
        $typeUser = "Employé";
        $msg = null;
        if($form->isSubmitted() && $form->isValid())
        {
            $getValue = $form->getData();
            $condition1 = $getListEmploye->findOneBy(['login' => $getValue->getLogin()]);
            $condition2 = $getListEmploye->findOneBy(['mdp' => $getValue->getMdp()]);
            if($condition1 == null || $condition2 == null){
                $msg = "Login ou mot de passe incorrect !";
                return $this->render('gsb/gestion_formations/connexion.html.twig', array('form'=>$form->createView(), 'msg' => $msg, 'typeUser' => $typeUser));;
            }

            if($condition1->getId() == $condition2->getId()){
                $id = $condition1->getId();
                $session = new Session();
                $session->set("id",$id);
                $session->set("typeUser", "employe");
                $session->get($id);
                $session->get("employe");
                $session->start();
                return $this->redirectToRoute('app_aff_tout');
            }
        }
        return $this->render('gsb/gestion_formations/connexion.html.twig', array('form'=>$form->createView(), 'msg' => $msg, 'typeUser' => $typeUser));
    }

    /**
     * @Route("/affListeFormations/", name="app_aff_tout")
     * Fonction permettant d’afficher la liste des formations côté employé
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
        return $this->render('gsb/gestion_formations/listeformation.html.twig', array('lesFormations'=>$formation,'message'=>$message, 'typeUser'=>$typeUser, 'deleteMsg'=>null));
    }

     /**
     * @Route("/suppFormation/{id}", name="app_supp_tout")
     * Fonction permettant de supprimer la formation en question
     */
    public function supprimerUneFormation($id, ObjectManager $manager)
    {
        $session = $this->container->get('session')->get('id');
        $typeUser = $this->container->get('session')->get('typeUser');
        $deleteMsg = null;
        if($session == null){
            return $this->redirectToRoute('app_identification');
        }
        $formation = $this->getDoctrine()->getRepository(Formation::class)->find($id);
        $getListInscription = $this->getDoctrine()->getRepository(Inscription::class);
        $condition=$getListInscription->findOneBy(['formation' => $formation]);
        if($condition != null)
        {
            return $this->redirectToRoute('app_aff_tout');
        }   
        else{
            $manager->remove($formation);
            $manager->flush();
            $deleteMsg = "La suppression a bien été effectuée !";
        }
        return $this->render('gsb/gestion_formations/listeformation.html.twig', array('lesFormations'=>$formation,'message'=>null, 'typeUser'=>$typeUser, 'deleteMsg'=>$deleteMsg));
    }

    /**
     * @Route("/ajoutFormation", name="app_formation_ajouter")
     * Fonction permettant d'ajouter une formation
     */
    public function ajoutformationAction(Request $request, $formation = null){
        $session = $this->container->get('session')->get('id');
        if($session == null){
            return $this->redirectToRoute('app_identification');
        }
        if($formation == null){
            $formation = new formation();
        }
        $form = $this->createForm(FormAjouterFormationType::class, $formation);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('app_aff_tout');
        }
        return $this->render('gsb/gestion_formations/ajouterFormation.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @Route("/affListeInscription/", name="aff_inscription")
     * Fonction permettant d'afficher la liste des inscriptions en cours
     */
    public function afficheLesInscriptions()
    {
        $session = $this->container->get('session')->get('id');
        $test = null;
        if($session == null){
            return $this->redirectToRoute('app_identification');
        }
        $listeInscription = $this->getDoctrine()->getRepository(Inscription::class);
        $test = $listeInscription->findBy(['statut' => "E"]);
        if(!$test){
            $message = "Aucune inscription en cours !";
        }
        else{
            $message = null;
        }
        return $this->render('gsb/gestion_formations/listeInscription.html.twig', array('lesInscriptions'=>$test,'message'=>$message));
    }

    /**
     * @Route("/refuser/{id}", name="app_refuser")
     * Fonction permettant de refuser une inscription
     */
    public function refuserInscription($id, ObjectManager $manager)
    {
        $inscription = $this->getDoctrine()->getRepository(Inscription::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $inscription->setStatut("R");
        $em->persist($inscription);
        $em->flush();
        return $this->redirectToRoute('aff_inscription');
    }

    /**
     * @Route("/accepter/{id}", name="app_accepter")
     * Fonction permettant d'accepter une inscription
     */
    public function accepter($id){
        $inscription = $this->getDoctrine()->getRepository(Inscription::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $inscription->setStatut("A");
        $em->persist($inscription);
        $em->flush();
        return $this->redirectToRoute('aff_inscription');
    }

    /**
     * @Route("/deconnexion/", name="app_deconnexion")
     * Fonction permettant de se déconnecter (détruit la session)
     */
    public function deconnexion(){
        $session = $this->container->get('session');
        $session->invalidate();
        return $this->redirectToRoute('app_bienvenue');
    }
}