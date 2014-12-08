<?php

namespace Htime\LightCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Htime\LightCmsBundle\Entity\Snippet;
use Htime\LightCmsBundle\Form\SnippetType;

/**
 * Class SnippetController
 *
 * @Route("/snippet")
 */
class SnippetController extends Controller
{
    /**
     * Liste tous les fragments de code.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/list", name="htime_light_cms_snippet_list")
     * @Template("HtimeLightCmsBundle:Snippet:list.html.twig")
     */
    public function listAction()
    {
        // On récupère l'Entity Manager
        $em = $this->getDoctrine()->getManager();

        // On récupère les fragments de code
        $snippets = $em->getRepository('HtimeLightCmsBundle:Snippet')->findBy(array(), array());

        return array('snippets' => $snippets);
    }

    /**
     * Affiche un fragment de code en détails.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/display/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_snippet_display")
     * @Template("HtimeLightCmsBundle:Snippet:display.html.twig")
     */
    public function displayAction($id)
    {
    	// On récupère l'Entity Manager
    	$em = $this->getDoctrine()->getManager();

    	// On récupère le fragment de code
    	$snippet = $em->getRepository('HtimeLightCmsBundle:Snippet')->find($id);

    	if ($snippet == null) {
    		throw $this->createNotFoundException('Fragment de code[id=' . $id . '] inexistant.');
    	}

        return array('snippet' => $snippet);
    }

    /**
     * Ajoute un nouveau fragment de code.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/add", name="htime_light_cms_snippet_add")
     * @Template("HtimeLightCmsBundle:Snippet:add.html.twig")
     */
    public function addAction()
    {
        $snippet = new snippet;

        // On créé le formulaire
        $form = $this->createForm(new SnippetType, $snippet);

        // On récupère la requête
        $request = $this->getRequest();

        // On vérifie qu'elle est de type POST
    	if ($request->getMethod() == 'POST') {
    		// On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $snippet contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {
                // On récupère l'Entity Manager
        		$em = $this->getDoctrine()->getManager();

        		// On enregistre le nouveau fragment de code
        		$em->persist($snippet);
        		$em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le fragment de code "' . $snippet->getName() . '" a été correctement créé');

        		// Puis on redirige vers la page de description du fragment de code nouvellement créé
        		return $this->redirect( $this->generateUrl('htime_light_cms_snippet_display', array('id' => $snippet->getId())) );
            }
    	}

    	// À ce stade :
		// - soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
        return array('form' => $form->createview());
    }

    /**
     * Modifie un fragment de code déjà existant.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_snippet_edit")
     * @ParamConverter("snippet", class="HtimeLightCmsBundle:Snippet", options={"id" = "id"})
     * @Template("HtimeLightCmsBundle:Snippet:edit.html.twig")
     */
    public function editAction(Snippet $snippet)
    {
        $form = $this->createForm(new SnippetType, $snippet);

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $snippet contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);

            if ($form->isValid()) {
                // On récupère l'Entity Manager
                $em = $this->getDoctrine()->getManager();

                // On enregistre les modifications apportées au fragment de code
                $em->persist($snippet);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le fragment de code "' . $snippet->getName() . '" a été correctement édité');

                // Puis on redirige vers la page de visualisation du fragment de code
                return $this->redirect($this->generateUrl('htime_light_cms_snippet_display', array('id' => $snippet->getId()) ));
            }
        }

        // À ce stade :
        // - soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
        return array('snippet' => $snippet, 'form' => $form->createView());
    }

    /**
     * Supprime un fragment de code.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_snippet_remove")
     * @ParamConverter("snippet", class="HtimeLightCmsBundle:Snippet", options={"id" = "id"})
     * @Template("HtimeLightCmsBundle:Snippet:remove.html.twig")
     */
    public function removeAction(Snippet $snippet)
    {
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression des fragments de code contre cette faille
        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // On supprime le fragment de code
                $em = $this->getDoctrine()->getManager();
                $em->remove($snippet);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le fragment de code "' . $snippet->getName() . '" a été correctement supprimé');

                // Puis on redirige vers la liste des fragments de code
                return $this->redirect($this->generateUrl('htime_light_cms_snippet_list'));
            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer le fragment de code
        return array('snippet' => $snippet, 'form' => $form->createView());
    }
}