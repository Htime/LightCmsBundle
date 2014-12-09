<?php

namespace Htime\LightCmsBundle\Manager;

use Doctrine\ORM\EntityManager;

class SnippetManager extends \Twig_Extension
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    protected $twigEnvironment;

    public function __construct(EntityManager $em, \Twig_Environment $twigEnvironment)
    {
        $this->em = $em;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function getFunctions()
    {
        return array(
            'snippet' => new \Twig_Function_Method(
                $this,
                'renderSnippet',
                array('is_safe' => array('html')) // On précise que le HTML peut être interprété pour cette extension
            )
        );
    }

    public function getName()
    {
        return 'HtimeLightCmsBundleSnippet';
    }

    public function renderSnippet($snippetName)
    {
        // On récupère le fragment de code en fonction de son nom
        $snippet = $this->em->getRepository('HtimeLightCmsBundle:Snippet')->findOneByName($snippetName);

        if ($snippet == null) {

            // Si le fragment de code n'existe pas, on renvoit null pour éviter une erreur et faire en sorte que la page se charge quand même
            return null;
            
        } else {

            // On indique à Twig qu'il doit interpréter du code en provenance d'une chaîne de caractères
            $this->twigEnvironment->setLoader(new \Twig_Loader_String());

            // On renvoie le contenu du snippet interprété en Twig
            return $this->twigEnvironment->render($snippet->getContent());
        }
    }
}