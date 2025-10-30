<?php declare(strict_types=1);

namespace Moorl\Captcha\Storefront\Framework\Captcha;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Captcha\AbstractCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CaptchaTo extends AbstractCaptcha
{
    final public const CAPTCHA_NAME = 'moorlCaptchaTo';
    final public const CAPTCHA_REQUEST_PARAMETER = 'moorl_captcha_to';
    final public const INVALID_CAPTCHA_CODE = 'captcha.basic-captcha-invalid';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SystemConfigService $systemConfigService
    )
    {
    }

    public function supports(Request $request, array $captchaConfig): bool
    {
        /** @var SalesChannelContext|null $context */
        $context = $request->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $salesChannelId = $context ? $context->getSalesChannelId() : null;

        $activeCaptchas = $this->systemConfigService->get('core.basicInformation.activeCaptchasV2', $salesChannelId);
        if (empty($activeCaptchas) || !\is_array($activeCaptchas)) {
            return false;
        }

        return $request->isMethod(Request::METHOD_POST)
            && \in_array(self::CAPTCHA_NAME, array_keys($activeCaptchas), true)
            && $activeCaptchas[self::CAPTCHA_NAME]['isActive'];
    }

    public function isValid(Request $request, array $captchaConfig): bool
    {
        $basicCaptchaValue = $request->get(self::CAPTCHA_REQUEST_PARAMETER);
        if ($basicCaptchaValue === null) {
            return false;
        }

        $session = $this->requestStack->getSession();
        $captchaSession = $session->get($basicCaptchaValue);

        if ($captchaSession === null) {
            return false;
        }

        try {
            $isValid = time() - (int) $captchaSession > (int) $captchaConfig['config']['timeout'];

            if (!(bool) $captchaConfig['config']['allowRetry']) {
                $session->remove($basicCaptchaValue);
            }
        } catch (\Exception) {
            $isValid = false;
        }

        if ($isValid) {
            $session->remove($basicCaptchaValue);
        }

        return $isValid;
    }

    public function shouldBreak(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return self::CAPTCHA_NAME;
    }

    public function getViolations(): ConstraintViolationList
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            '',
            '',
            [],
            '',
            '/' . self::CAPTCHA_REQUEST_PARAMETER,
            '',
            null,
            self::INVALID_CAPTCHA_CODE
        ));

        return $violations;
    }
}
