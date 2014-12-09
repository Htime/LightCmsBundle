<?php

namespace Htime\LightCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Htime\LightCmsBundle\Entity\Media;
use Htime\LightCmsBundle\Form\MediaType;

/**
 * Class MediaController
 *
 * @Route("/media")
 */
class MediaController extends Controller
{
    /**
     * Liste tous les médias.
     *
     * @Security("has_role('ROLE_LIGHT_CMS_ADMIN')")
     *
     * @Route("/list", name="htime_light_cms_media_list")
     * @Template("HtimeLightCmsBundle:Media:list.html.twig")
     */
    public function listAction()
    {
        // On récupère l'Entity Manager
        $em = $this->getDoctrine()->getManager();

        // On récupère les médias
        $medias = $em->getRepository('HtimeLightCmsBundle:Media')->findBy(array(), array());

        return array('medias' => $medias);
    }

    /**
     * Affiche un média en détails.
     *
     * @Security("has_role('ROLE_LIGHT_CMS_ADMIN')")
     *
     * @Route("/display/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_media_display")
     * @Template("HtimeLightCmsBundle:Media:display.html.twig")
     */
    public function displayAction($id)
    {
    	// On récupère l'Entity Manager
    	$em = $this->getDoctrine()->getManager();

    	// On récupère le média
    	$media = $em->getRepository('HtimeLightCmsBundle:Media')->find($id);

    	if ($media == null) {
    		throw $this->createNotFoundException('Média[id=' . $id . '] inexistant.');
    	}

        return array('media' => $media);
    }

    /**
     * Ajoute un nouveau média.
     *
     * @Security("has_role('ROLE_LIGHT_CMS_ADMIN')")
     *
     * @Route("/add/{name}", requirements={"name" = "\w*"}, name="htime_light_cms_media_add")
     * @Template("HtimeLightCmsBundle:Media:add.html.twig")
     */
    public function addAction($name = null)
    {
        $media = new Media;

        $media->setName($name);

        // On créé le formulaire
        $form = $this->createForm(new MediaType, $media);

        // On récupère la requête
        $request = $this->getRequest();

        // On vérifie qu'elle est de type POST
    	if ($request->getMethod() == 'POST') {
    		// On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $media contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {
                // On récupère l'Entity Manager
        		$em = $this->getDoctrine()->getManager();

        		// On enregistre le nouveau média
        		$em->persist($media);
        		$em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le média "' . $media->getName() . '" a été correctement créé.');

        		// Puis on redirige vers la page de description du média nouvellement créé
        		return $this->redirect( $this->generateUrl('htime_light_cms_media_display', array('id' => $media->getId())) );
            }
    	}

    	// À ce stade :
		// - soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
        return array('form' => $form->createview());
    }

    /**
     * Modifie un média déjà existant.
     *
     * @Security("has_role('ROLE_LIGHT_CMS_ADMIN')")
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_media_edit")
     * @ParamConverter("media", class="HtimeLightCmsBundle:Media", options={"id" = "id"})
     * @Template("HtimeLightCmsBundle:Media:edit.html.twig")
     */
    public function editAction(Media $media)
    {
        $form = $this->createForm(new MediaType, $media);

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $media contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);

            if ($form->isValid()) {
                // On récupère l'Entity Manager
                $em = $this->getDoctrine()->getManager();

                // On enregistre les modifications apportées au média
                $em->persist($media);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le média "' . $media->getName() . '" a été correctement édité.');

                // Puis on redirige vers la page de visualisation du média
                return $this->redirect($this->generateUrl('htime_light_cms_media_display', array('id' => $media->getId()) ));
            }
        }

        // À ce stade :
        // - soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
        return array('media' => $media, 'form' => $form->createView());
    }

    /**
     * Supprime un média.
     *
     * @Security("has_role('ROLE_LIGHT_CMS_ADMIN')")
     *
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="htime_light_cms_media_remove")
     * @ParamConverter("media", class="HtimeLightCmsBundle:Media", options={"id" = "id"})
     * @Template("HtimeLightCmsBundle:Media:remove.html.twig")
     */
    public function removeAction(Media $media)
    {
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression des médias contre cette faille
        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // On supprime le média
                $em = $this->getDoctrine()->getManager();
                $em->remove($media);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('success', 'Le média "' . $media->getName() . '" a été correctement supprimé.');

                // Puis on redirige vers la liste des médias
                return $this->redirect($this->generateUrl('htime_light_cms_media_list'));
            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer le média
        return array('media' => $media, 'form' => $form->createView());
    }
}