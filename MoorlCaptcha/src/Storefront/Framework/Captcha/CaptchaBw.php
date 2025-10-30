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

class CaptchaBw extends AbstractCaptcha
{
    final public const CAPTCHA_NAME = 'moorlCaptchaBw';
    final public const CAPTCHA_REQUEST_PARAMETER = 'moorl_captcha_bw';
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
        if (!isset($captchaConfig['config']['rules'])) {
            return false;
        }

        if (!is_array($captchaConfig['config']['rules'])) {
            return false;
        }

        $rules = $captchaConfig['config']['rules'];

        $isValid = true;

        foreach ($rules as $rule) {
            if (!$isValid) {
                return false;
            }

            $isValid = $this->checkRule($rule, $request->request->all());
        }

        return $isValid;
    }

    private function checkRule(array $rule, array $inputValues): bool
    {
        if (empty($rule['active']) || empty($rule['name']) || empty($rule['regex'])) {
            return true;
        }

        if ($rule['name'] === '*') {
            $inputFields = array_keys($inputValues);
        } else {
            $inputFields = explode(",", $rule['name']);
        }

        foreach ($inputFields as $inputField) {
            if (!isset($inputValues[$inputField])) {
                continue;
            }

            if (is_array($inputValues[$inputField]) || str_contains($inputField, 'password')) {
                continue;
            }

            $regexes = explode(PHP_EOL, $rule['regex']);
            foreach ($regexes as $regex) {
                if (preg_match("/" . trim($regex) . "/iu", $inputValues[trim($inputField)])) {
                    return false;
                }
            }
        }

        return true;
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
