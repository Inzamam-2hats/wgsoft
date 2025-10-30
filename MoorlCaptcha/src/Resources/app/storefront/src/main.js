import MoorlCaptchaToPlugin from './captcha-to/captcha-to.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('MoorlCaptchaTo', MoorlCaptchaToPlugin, '[data-moorl-captcha-to]');

if (module.hot) {
    module.hot.accept();
}
