<?php

namespace Htime\LightCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class TestController
 *
 * @Route("/")
 */
class TestController extends Controller
{
	/**
     * Page de test.
     *
     * PreAuthorize("hasRole('ROLE_ADMIN')")
     *
     * @Route("/test", name="htime_light_cms_test")
     * @Template("HtimeLightCmsBundle:Test:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}
