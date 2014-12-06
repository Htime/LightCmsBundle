<?php

namespace Htime\LightCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        return $this->render('HtimeLightCmsBundle:Test:index.html.twig');
    }
}
