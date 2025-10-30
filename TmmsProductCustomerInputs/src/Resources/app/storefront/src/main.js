import SaveCustomerInputPlugin from './savecustomerinput-plugin/savecustomerinput-plugin.plugin';
import RepeatOrderSaveCustomerInputPlugin from './repeatordersavecustomerinput-plugin/repeatordersavecustomerinput-plugin.plugin';

const PluginManager = window.PluginManager;

PluginManager.register('SaveCustomerInputPlugin', SaveCustomerInputPlugin, '[data-save-customer-input="true"]');
PluginManager.register('RepeatOrderSaveCustomerInputPlugin', RepeatOrderSaveCustomerInputPlugin);
