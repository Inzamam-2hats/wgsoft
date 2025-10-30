import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

import './component/lws-esd-serials-listing'
import './component/lws-esd-serials-detail'


Module.register('lws-esd-serials', {
    type: 'plugin',
    name: 'lws-esd-serials',
    color: '#57D9A3',
    icon: 'regular-products',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    }
});
