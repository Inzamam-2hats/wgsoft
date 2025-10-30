import template from './extension/sw-order-line-items-grid/sw-order-line-items-grid.html.twig';
import './extension/sw-order-line-items-grid/sw-order-line-items-grid.scss';

const { Component } = Shopware;

Component.override('sw-order-line-items-grid', {
    template
});
