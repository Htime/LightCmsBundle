<?php

namespace Htime\LightCmsBundle\Manager;

use Doctrine\ORM\EntityManager;
use Htime\LightCmsBundle\Entity\Media;

class MediaManager extends \Twig_Extension
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            'media' => new \Twig_Function_Method(
                $this,
                'displayWebMedia'
            )
        );
    }

    public function getName()
    {
        return 'HtimeLightCmsBundleMedia';
    }

    public function displayWebMedia($mediaName)
    {
        // On récupère le média
        $media = $this->em->getRepository('HtimeLightCmsBundle:Media')->findOneByName($mediaName);

        if ($media == null) {
            // Si le média est introuvable, on retourne une page qui n'existe pas et qui déclenche une erreur 404
            return '/erreur';
        } else {
            // Sinon, on renvoie le lien vers le fichier
            return '/' . Media::getWebDir() . '/' . $media->getPath();
        }
    }
}