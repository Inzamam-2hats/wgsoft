<?php declare(strict_types=1);

namespace Moorl\Captcha\Storefront\Controller;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CaptchaController extends StorefrontController
{
    #[Route(path: '/moorl-captcha-to', name: 'moorl-captcha.to', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function captchaTo(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $value = Uuid::randomHex();
        $session->set($value, time());

        $response = new JsonResponse(['value' => $value]);
        $response->headers->set('x-robots-tag', 'noindex, nofollow');

        return $response;
    }
}
