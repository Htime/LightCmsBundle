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

            // Si le média n'existe pas, on renvoit null pour éviter une erreur et faire en sorte que la page se charge quand même
            return null;

        } else {
            
            // Sinon, on renvoie le lien vers le fichier
            return '/' . Media::getWebDir() . '/' . $media->getPath();
        }
    }
}