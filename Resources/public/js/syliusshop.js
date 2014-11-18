(function ($, document, window) {
    this.doc = $(document);
    this.settings = {
        log: false,
        hinclude: {
            cartId: 'cart'
        },
        handlers: {
            'strategyServiceForm': {
                'dataSelector': 'strategy-form'
            },
            'invoiceSameAsDelivery': {
                'dataContainerSelector': 'invoice-same-as-delivery-container',
                'dataSelector': 'invoice-same-as-delivery',
                'dataDeliveryAddressSelector': 'delivery-address'
            }
        },
        messageModal: {
            'dataSelector': 'message-modal-event',
            'dataEventTypeSelector': 'message-modal-event-type'
        },
        actions: {
            'cartChanged': {
                'eventName': 'syliusshop.cart.changed'
            },
            'watchlistChanged': {
                'eventName': 'syliusshop.watchlist.changed'
            },
            'cartItemAdd': {
                'dataSelector': 'cart-item-add-action',
                'eventName': 'syliusshop.cart.item.doadd',
                'dataQuantity': 'quantity',
                'defaultQuantity': 1,
                'eventErrorName': 'syliusshop.cart.item.adderror',
                'eventSuccessName': 'syliusshop.cart.item.addsuccess'
            },
            'watchlistItemAdd': {
                'dataSelector': 'watchlist-item-add-action',
                'eventName': 'syliusshop.watchlist.item.doadd',
                'eventErrorName': 'syliusshop.watchlist.item.adderror',
                'eventSuccessName': 'syliusshop.watchlist.item.addsuccess'
            },
            'watchlistItemRemove': {
                'dataSelector': 'watchlist-item-remove-action',
                'eventName': 'syliusshop.watchlist.item.doremove',
                'eventErrorName': 'syliusshop.watchlist.item.removeerror',
                'eventSuccessName': 'syliusshop.watchlist.item.removesuccess',
                'dataContainerRemoveSelect': 'watchlist-remove-container'
            },
            'quantityChange': {
                'dataSelector': 'quantity-change',
                'eventName': 'syliusshop.quantity.change',
                'dataContainerSelector': 'quantity-change-container',
                'dataInputSelector': 'quantity-input-container',
                'inputSelector': 'input'
            }
        },
        strategy: {
            selectors: {
                strategies: '[data-strategies]'
            }
        }
    };

    this.console = window.console || {
        log: function (msg) {
        }
    };
    this.hinclude = window.hinclude || null;
    this.messageModals = {};

    var self = window.syliusShop = this;

    /**
     * Helper for log
     * @param {mixed} msg
     */
    this.log = function (msg) {
        if (!self.settings.log == true) {
            return;
        }
        self.console.log(msg);
    };

    this.onAction = function (eventName, cb, type) {
        var settings = self.settings.actions;
        if (typeof settings[eventName] == 'undefined') {
            throw "eventName " + eventName + " not available";
        }

        type = type ? this.ucfirst(type) : '';
        var key = 'event' + type + 'Name';
        eventName = settings[eventName][key];

        if (typeof eventName == 'undefined') {
            throw "eventName " + eventName + " not available";
        }

        self.log('Register Event ' + eventName);
        this.doc.on(eventName, cb);
    };

    /**
     * @param str
     * @returns {string}
     */
    this.ucfirst = function (str) {
        str += '';
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1);
    };

    /**
     * Helper for log events
     * @param {string} eventName
     * @param {array} parameters
     */
    this.trigger = function (eventName, parameters) {
        self.log('syliusShop.trigger -> ' + eventName);
        self.log(parameters);

        self.doc.trigger(eventName, parameters);
    };

    /**
     * Merge Settings with given Options
     * @param {object} options
     */
    this.setup = function (options) {
        self.settings = $.extend({}, this.settings, options);

        self.log('syliusShop.setup');
        self.log(self.settings);
    };

    this.registerAll = function () {
        self.log('syliusShop.registerAll');

        self.registerEvents();
        self.registerListeners();
        self.registerHandlers();
        self.registerMessageModals();
    };

    this.registerMessageModals = function () {
        var settings = self.settings.messageModal;

        $('[data-message-modal-event]').each(function () {
            var modal = $(this);

            var eventName = modal.data(settings.dataSelector);
            var eventType = modal.data(settings.dataEventTypeSelector);

            self.registerMessageModal(eventName, modal, modal.data(), eventType);
        });
    };

    /**
     * Register all Events
     * - Cart Events
     * - Watchlist Events
     * - QuantityChange Events
     */
    this.registerEvents = function () {
        self.log('syliusShop.registerEvents')

        self.registerCartEvents();
        self.registerWatchlistEvents();
        self.registerQuantityChangeEvents();
    };

    /**
     * Register all Listeners
     */
    this.registerListeners = function () {
        self.log('syliusShop.registerListeners')

        self.registerCartListener();
        self.registerWatchlistListener();
        self.registerQuantityChangeListener();
    };

    this.registerHandlers = function () {
        self.log('syliusShop.registerHandlers');

        self.registerStrategyServiceFormHandler();
        self.registerInvoiceSameAsDeliveryHandler();
    };

    /**
     * Cart Events
     * itemAdd = evaluate url, itemid and quantity, trigger cart item add event
     */
    this.registerCartEvents = function () {
        self.log('syliusShop.registerCartEvents');

        var doc = self.doc;
        var settings = self.settings;

        doc.on('click', '[data-' + settings.actions.cartItemAdd.dataSelector + ']', function (e) {
            e.preventDefault();

            var item = $(this);

            var url = item.attr('href');
            var itemId = item.data(settings.actions.cartItemAdd.dataSelector);
            var quantity = self.getItemQuantity(item);

            self.trigger(settings.actions.cartItemAdd.eventName, [url, itemId, quantity, item]);
        });
    };

    /**
     * Watchlist Events
     * itemAdd = evaluate url and itemid, trigger watchlist item add event
     * itemRemove = evaluate url and itemid, trigger watchlist item remove event
     */
    this.registerWatchlistEvents = function () {
        self.log('syliusShop.registerWatchlistEvents');

        var doc = self.doc;

        $.each([self.settings.actions.watchlistItemAdd, self.settings.actions.watchlistItemRemove], function (index, settings) {
            doc.on('click', '[data-' + settings.dataSelector + ']', function (e) {
                e.preventDefault();

                var item = $(this);
                var url = item.attr('href');
                var itemId = item.data(settings.dataSelector);

                self.trigger(settings.eventName, [url, itemId, item]);
            });
        });
    };

    /**
     * QuantityChange Events
     * QuantityChangeClick = find quantityInput in container, read quantityChange (+- X), trigger quantityChangeEvent
     */
    this.registerQuantityChangeEvents = function () {
        self.log('syliusShop.registerQuantityChangeEvents');

        var doc = self.doc;
        var settings = self.settings.actions.quantityChange;

        doc.on('click', '[data-' + settings.dataSelector + ']', function (e) {
            e.preventDefault();

            var elem = $(this);
            var quantityInput = elem
                .closest('[data-' + settings.dataContainerSelector + ']')
                .find('[data-' + settings.dataInputSelector + '] ' + settings.inputSelector + ':first');

            var quantityChange = parseInt(elem.data(settings.dataSelector));
            var newQuantity = parseInt(quantityInput.val()) + quantityChange;

            if (newQuantity < 0) {
                newQuantity = 0;
            }

            self.trigger(settings.eventName, [quantityInput, newQuantity]);
        });
    };

    this.registerMessageModal = function (name, modal, settings, eventType) {
        self.log('syliusShop.registerMessageModal: ' + name);

        var defaultSettings = {
            mouseOutTimeout: 200,
            showTimeout: 2000
        };

        settings = $.extend({}, defaultSettings, settings);

        if (!this.messageModals[name]) {
            this.messageModals[name] = [];
        }

        this.messageModals[name][eventType] = {
            container: modal,
            settings: settings,
            timeout: null
        };

        self.log(settings);

        this.registerMessageModalEvents(name, eventType);
        this.registerMessageModalListeners(name, eventType);
    };

    this.registerMessageModalListeners = function (name, eventType) {
        self.log('syliusShop.registerMessageModalListeners: ' + name);

        this.onAction(name, function (e) {
            self.showMessageModal(name, eventType);
        }, eventType);
    };

    this.registerMessageModalEvents = function (name, type) {
        self.log('syliusShop.registerMessageModalEvents: ' + name);

        var modal = this.messageModals[name][type];
        var container = modal.container;

        container.mouseover(function () {
            self.log('MessageModal mouseover ' + name);
            self.log('MessageModal clearTimeout ' + name);

            clearTimeout(modal.timeout);
        }).mouseout(function () {
            self.log('MessageModal mouseout ' + name);
            self.log('MessageModal setTimeout ' + name);

            modal.timeout = window.setTimeout(function () {
                container.fadeOut();
                self.log('MessageModal fadeOut ' + name);
            }, modal.settings.mouseOutTimeout);
        });
    };

    this.showMessageModal = function (name, type) {
        var modal = this.messageModals[name][type];
        var container = modal.container;

        clearTimeout(container.timeout);
        container.stop();
        container.fadeIn();

        modal.timeout = window.setTimeout(function () {
            container.fadeOut();
        }, modal.settings.showTimeout);
    };

    /**
     * Evaluate ItemQuantity for add Item to Cart
     * @param {jQuery} item
     * @returns int
     */
    this.getItemQuantity = function (item) {
        self.log('syliusShop.getItemQuantity:');

        var settings = self.settings.actions.cartItemAdd;
        var itemActionQuantity = parseInt(item.data(settings.dataQuantity));

        if (itemActionQuantity >= 1) {
            self.log('Found quantity on action item: ' + itemActionQuantity);
            return itemActionQuantity;
        }

        var quantityChangeSettings = self.settings.actions.quantityChange;
        var itemInput = item
            .closest('[data-' + quantityChangeSettings.dataContainerSelector + ']')
            .find('[data-' + quantityChangeSettings.dataInputSelector + '] ' + quantityChangeSettings.inputSelector + ':first');

        var itemInputQuantity = parseInt(itemInput.val());
        if (itemInputQuantity >= 1) {
            self.log('Found quantity in quantityChangeContainer input field: ' + itemInputQuantity);
            return itemInputQuantity;
        }

        self.log('Default Quantity: ' + settings.defaultQuantity);
        return settings.defaultQuantity;
    };

    /**
     * QuantityChangeListener
     * - QuantityChange = QuantityInput set value and trigger change()
     */
    this.registerQuantityChangeListener = function () {
        self.log('syliusShop.registerQuantityChangeListener');

        self.doc.on(self.settings.actions.quantityChange.eventName, function (e, quantityInput, newQuantity) {
            quantityInput.val(newQuantity).change();
        });
    };

    /**
     * Cart Listeners
     * - CartChange = Hinclude Refresh
     * - ItemAdd = Post to Url, trigger cart Changed event or itemAdd error event
     */
    this.registerCartListener = function () {
        self.log('syliusShop.registerCartListener');

        var doc = self.doc;
        var settings = self.settings;

        doc.on(settings.actions.cartItemAdd.eventSuccessName, function (e, data, statusText, url, itemId, quantity, item) {
            self.trigger(settings.actions.cartChanged.eventName, [data, statusText, url, itemId, quantity, item]);
        });

        doc.on(settings.actions.cartChanged.eventName, function (e, data, statusText, url, itemId, quantity, item) {
            if (typeof self.hinclude == 'undefined') {
                self.log('hinclude lib not found');
                return;
            }

            self.log('refresh hinclude id ' + settings.hinclude.cartId);
            self.hinclude.refresh(settings.hinclude.cartId);
        });

        doc.on(settings.actions.cartItemAdd.eventName, function (e, url, itemId, quantity, item) {
            self.log('POST to ' + url + ' with itemId ' + itemId + ' with quantity ' + quantity);

            $.ajax(url, {
                type: 'POST',
                data: {
                    itemId: itemId,
                    quantity: quantity
                },
                success: function (data, statusText) {
                    self.log(statusText);
                    self.trigger(settings.actions.cartItemAdd.eventSuccessName, [data, statusText, url, itemId, quantity, item]);
                },
                error: function (data, statusText) {
                    self.log(statusText);
                    self.trigger(settings.actions.cartItemAdd.eventErrorName, [data, statusText, url, itemId, quantity, item]);
                }
            });
        });
    };

    /**
     * Watchlist Listeners
     * - ItemAdd = Post to Url, trigger watchlist changed event or itemAdd error event
     * - ItemRemove = Post to Url, trigger watchlist changed event or itemRemove error event
     * - ItemRemoveSuccess = Remove Container from DOM
     */
    this.registerWatchlistListener = function () {
        var doc = self.doc;
        var settings = self.settings;

        doc.on(settings.actions.watchlistItemAdd.eventName, function (e, url, itemId, item) {
            self.log('POST to ' + url + ' with itemId ' + itemId);

            $.ajax(url, {
                method: 'POST',
                data: {
                    itemId: itemId
                },
                success: function (data, statusText) {
                    self.trigger(settings.actions.watchlistItemAdd.eventSuccessName, [data, statusText, url, itemId, item]);
                },
                error: function (data, statusText) {
                    self.trigger(settings.actions.watchlistItemAdd.eventErrorName, [data, statusText, url, itemId, item]);
                }
            });
        });

        doc.on(settings.actions.watchlistItemRemove.eventName, function (e, url, itemId, item) {
            self.log('POST to ' + url + ' with itemId ' + itemId);

            $.ajax(url, {
                method: 'POST',
                data: {
                    itemId: itemId
                },
                success: function (data, statusText) {
                    self.trigger(settings.actions.watchlistItemRemove.eventSuccessName, [data, statusText, url, itemId, item]);
                },
                error: function (data, statusText) {
                    self.trigger(settings.actions.watchlistItemRemove.eventErrorName, [data, statusText, url, itemId, item]);
                }
            });
        });

        doc.on(settings.actions.watchlistItemRemove.eventSuccessName, function (e, data, statusText, url, itemId, item) {
            var selector = '[data-' + settings.actions.watchlistItemRemove.dataContainerRemoveSelect + ']';
            self.log('remove closest to item container from dom: ' + selector);

            item.closest(selector).remove();
        });
    };

    /**
     * Strategy Form Handler
     */
    this.registerStrategyServiceFormHandler = function () {
        self.log('syliusShop.registerStrategyFormHandler');

        $('form[data-' + self.settings.handlers.strategyServiceForm.dataSelector + ']').each(function () {
            self.log('Found ServiceForm');

            var form = $(this);
            var strategies = form.find('[data-strategies]');

            strategies
                .find('[data-strategy] [data-parent]:visible :input:not(:checked)')
                .closest('[data-strategy]')
                .find('[data-child]')
                .hide();

            form.find(':submit').click(function (e) {
                self.log('StrategyChoice Form submit -> disable all unneeded inputs');
                strategies
                    .find('[data-strategy] [data-parent] :input:not(:checked)')
                    .closest('[data-strategy]')
                    .find('[data-child] :input')
                    .prop('disabled', 'disabled');
            });

            var strategieChoiceParentInputs = strategies.find('[data-strategy] [data-parent] :input');

            strategieChoiceParentInputs.change(function () {
                self.log('StrategyChoice Parent Input changed');

                strategieChoiceParentInputs.each(function () {
                    var elem = $(this);

                    var elemIsChecked = elem.is(':checked');
                    var childForm = elem.closest('[data-strategy]').find('[data-child]');

                    if (elemIsChecked || elem.closest('[data-parent]').is(':hidden')) {
                        childForm.show();
                    } else {
                        childForm.hide();
                    }

                    if (!elemIsChecked) {
                        childForm.find(':input').prop('checked', false);
                    }
                });
            });

            strategies.find('[data-strategy] [data-child] :input').change(function () {
                self.log('StrategyChoice Child changed');

                $(this)
                    .closest('[data-strategy]')
                    .find('[data-parent] :input')
                    .prop('checked', 'checked')
                    .change();
            });
        });
    };

    /**
     * Invoice Same As Delivery Handler
     */
    this.registerInvoiceSameAsDeliveryHandler = function () {
        self.log('syliusShop.registerInvoiceSameAsDeliveryHandler');

        var settings = self.settings.handlers.invoiceSameAsDelivery;

        $('[data-' + settings.dataContainerSelector + ']').each(function () {
            self.log('found invoiceSameAsDelivery');

            var container = $(this);
            var addressDelivery = container.find('[data-' + settings.dataDeliveryAddressSelector + ']');
            var addressDeliveryInputs = addressDelivery.find(':input');
            var toggleInputContainer = container.find('[data-' + settings.dataSelector + ']');

            var handleChange = function (doSlide) {
                if (parseInt(toggleInputContainer.find('input:checked').val()) == 1) {
                    self.log('addressDeliveryInputs disabled');

                    addressDeliveryInputs.prop("disabled", "disabled");
                    if (doSlide == true) {
                        addressDelivery.slideUp();
                    } else {
                        addressDelivery.hide();
                    }
                } else {
                    self.log('addressDeliveryInputs available');

                    addressDeliveryInputs.prop("disabled", false);
                    if (doSlide == true) {
                        addressDelivery.slideDown();
                    } else {
                        addressDelivery.show();
                    }
                }
            };

            toggleInputContainer.find('input').change(function (e) {
                handleChange(true);
            });

            handleChange(false);
        });
    };
}(jQuery, document, window));