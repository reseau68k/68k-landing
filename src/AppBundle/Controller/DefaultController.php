<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

use AppBundle\Entity\Preinscription;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request) {
        return $this->render('AppBundle::hello.html.twig', array(
            'inscrits' => $this->getInscrits()
        ));
    }

    /**
     * @Route("/", name="submit")
     * @Method("POST")
     */
    public function submitAction(Request $request) {

        $validator = $this->get('validator');
        $em = $this->get('doctrine.orm.entity_manager');

        $email = strtolower(trim($request->request->get('email')));
        $nom = trim($request->request->get('nom'));

        $valid = true;

        if($email === '' || count($validator->validate($email, new EmailConstraint())) > 0) {
            $this->addFlash('error', 'L\'email indiqué est invalide.');
            $valid = false;
        } else {
            # L'email est valide
            # On vérifie qu'il est bien unique

            $existing = $em->getRepository('AppBundle:Preinscription')->findByEmail($email);
            if(count($existing) > 0) {
                $this->addFlash('error', 'L\'email indiqué est déjà inscrit.');
                $valid = false;
            }
        }

        if($nom === '') {
            $this->addFlash('error', 'Le nom est obligatoire.');
            $valid = false;
        }

        if($valid) {
            $record = new Preinscription();
            $record->setCreatedat(new \DateTime());
            $record->setEmail($email);
            $record->setNom($nom);

            $em->persist($record);
            $em->flush();

            return new RedirectResponse('/done');
        }

        return $this->render('AppBundle::hello.html.twig', [
            'email' => $email,
            'nom' => $nom,
            'inscrits' => $this->getInscrits()
        ]);
    }

    /**
     * @Route("/done", name="done")
     */
    public function doneAction(Request $request) {
        return $this->render('AppBundle::hello.html.twig', [
            'done' => true,
            'inscrits' => $this->getInscrits()
        ]);
    }

    protected function getInscrits() {
        return array('Net Gusto', 'Bob <strong>Ballard</strong>');
    }
}
