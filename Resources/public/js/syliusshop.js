(function($, document, window, console, hinclude){
    this.doc = $(document);
    this.settings = {
        log: true,
        hinclude: {
            cartId: 'cart'
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
                'dataItemAddContainer': 'item-add-container',
                'dataItemAddInputSelector': 'item-add-input',
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
    this.console = console || {log: function(msg){}};
    this.hinclude = hinclude;

    /**
     * Helper for log
     * @param mixed msg
     */
    this.log = function(msg){
        if(!self.settings.log == true){
            return;
        }
        self.console.log(msg);
    };

    /**
     * Helper for log events
     * @param string eventName
     * @param array parameters
     */
    this.trigger = function(eventName, parameters){
        self.log('syliusShop.trigger -> '+ eventName);
        self.log(parameters);

        self.doc.trigger(eventName, parameters);
    };

    /**
     * Merge Settings with given Options
     * @param object options
     */
    this.setup = function(options){
        self.settings = $.extend({}, this.settings, options);

        self.log('syliusShop.setup');
        self.log(this.settings);
    };

    /**
     * Register all Events
     */
    this.registerEvents = function(){
        self.log('syliusShop.registerEvents')

        var doc = self.doc;
        var settings = self.settings;

        self.registerCartEvents();
        self.registerWatchlistEvents();
        self.registerQuantityChangeEvents();
    };

    /**
     * Register all Listeners
     */
    this.registerListeners = function(){
        self.log('syliusShop.registerListeners')

        self.registerCartListener();
        self.registerWatchlistListener();
        self.registerQuantityChangeListener();
    };

    /**
     * Cart Events
     * itemAdd = evaluate url, itemid and quantity, trigger cart item add event
     */
    this.registerCartEvents = function(){
        self.log('syliusShop.registerCartEvents');

        var doc = self.doc;
        var settings = self.settings;

        doc.on('click', '[data-'+settings.actions.cartItemAdd.dataSelector+']', function(e){
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
    this.registerWatchlistEvents = function(){
        self.log('syliusShop.registerWatchlistEvents');

        var doc = self.doc;
        var settings = self.settings;

        doc.on('click', '[data-'+settings.actions.watchlistItemAdd.dataSelector+']', function(e){
            e.preventDefault();

            var item = $(this);
            var url = item.attr('href');
            var itemId = item.data(settings.actions.watchlistItemAdd.dataSelector);

            self.trigger(settings.actions.watchlistItemAdd.eventName, [url, itemId, item]);
        });

        doc.on('click', '[data-'+settings.actions.watchlistItemRemove.dataSelector+']', function(e){
            e.preventDefault();

            var item = $(this);
            var url = item.attr('href');
            var itemId = item.data(settings.actions.watchlistItemRemove.dataSelector);

            self.trigger(settings.actions.watchlistItemRemove.eventName, [url, itemId, item]);
        });
    };

    /**
     * QuantityChange Events
     * QuantityChangeClick = find quantityInput in container, read quantityChange (+- X), trigger quantityChangeEvent
     */
    this.registerQuantityChangeEvents = function(){
        self.log('syliusShop.registerQuantityChangeEvents');

        var doc = self.doc;
        var settings = self.settings;

        doc.on('click', '[data-'+settings.actions.quantityChange.dataSelector+']', function(e){
            e.preventDefault();

            var elem = $(this);
            var quantityInput = elem
                .closest('[data-'+ settings.actions.quantityChange.dataContainerSelector +']')
                .find('[data-'+ settings.actions.quantityChange.dataInputSelector +'] '+ settings.actions.quantityChange.dataInputSelector);

            var quantityChange = parseInt(elem.data(settings.actions.quantityChange.dataSelector));
            var newQuantity = parseInt(quantityInput.val())+quantityChange;

            if(newQuantity < 0){
                newQuantity = 0;
            }

            self.trigger(settings.actions.quantityChange.eventName, [quantityInput, newQuantity]);
        });
    };

    /**
     * Evaluate ItemQuantity for add Item to Cart
     * @param jQuery item
     * @returns int
     */
    this.getItemQuantity = function(item){
        self.log('syliusShop.getItemQuantity:');

        var settings = self.settings;
        var itemActionQuantity = parseInt(item.data(settings.actions.cartItemAdd.dataQuantity));

        if(itemActionQuantity >= 1){
            self.log(itemActionQuantity);
            return itemActionQuantity;
        }

        var itemInput = item
            .closest('[data-'+ settings.actions.cartItemAdd.dataItemAddContainer +']')
            .find('[data-'+ settings.actions.cartItemAdd.dataItemAddInputSelector +']');

        var itemInputQuantity = parseInt(itemInput.val());
        if(itemInputQuantity >= 1){
            self.log(itemInputQuantity);
            return itemInputQuantity;
        }

        self.log(settings.actions.cartItemAdd.defaultQuantity);
        return settings.actions.cartItemAdd.defaultQuantity;
    };

    /**
     * QuantityChangeListener
     * - QuantityChange = QuantityInput set value and trigger change()
     */
    this.registerQuantityChangeListener = function(){
        self.log('syliusShop.registerQuantityChangeListener');

        self.doc.on(self.settings.actions.quantityChange.eventName, function(e, quantityInput, newQuantity){
            quantityInput.val(newQuantity).change();
        });
    };

    /**
     * Cart Listeners
     * - CartChange = Hinclude Refresh
     * - ItemAdd = Post to Url, trigger cart Changed event or itemAdd error event
     */
    this.registerCartListener = function(){
        self.log('syliusShop.registerCartListener');

        var doc = self.doc;
        var settings = self.settings;

        doc.on(settings.actions.cartItemAdd.eventSuccessName, function(e, data, textStatus, url, itemId, quantity, item){
            if(typeof self.hinclude == 'undefined'){
                self.log('hinclude lib not found');
                return;
            }
            self.log('refresh hinclude id '+ settings.hinclude.cartId);
            self.hinclude.refresh(settings.hinclude.cartId);

            self.trigger(settings.actions.cartChanged.eventName, [data, textStatus, url, itemId, quantity, item]);
        });

        doc.on(settings.actions.cartItemAdd.eventName, function(e, url, itemId, quantity, item){
            self.log('POST to '+ url +' with itemId '+ itemId + ' with quantity '+ quantity);
            $.post(url, {
                itemId: itemId,
                quantity: quantity
            }, function(data, textStatus){
                self.log('Response received');
                if(textStatus == "success"){
                    self.trigger(settings.actions.cartItemAdd.eventSuccessName, [data, textStatus, url, itemId, quantity, item]);
                }else{
                    self.trigger(settings.actions.cartItemAdd.eventErrorName, [data, textStatus, url, itemId, quantity, item]);
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
    this.registerWatchlistListener = function(){
        var doc = self.doc;
        var settings = self.settings;

        doc.on(settings.actions.watchlistItemAdd.eventName, function(e, url, itemId, item){
            self.log('POST to '+ url +' with itemId '+ itemId);
            $.post(url, {
                itemId: itemId
            }, function(data, textStatus){
                self.log('Response received');
                if(textStatus == "success"){
                    self.trigger(settings.actions.watchlistItemAdd.eventSuccessName, [data, textStatus, url, itemId, item]);
                }else{
                    self.trigger(settings.actions.watchlistItemAdd.eventErrorName, [data, textStatus, url, itemId, item]);
                }
            });
        });

        doc.on(settings.actions.watchlistItemRemove.eventName, function(e, url, itemId, item){
            self.log('POST to '+ url +' with itemId '+ itemId);
            $.post(url, {
                itemId: itemId
            }, function(data, textStatus){
                self.log('Response received');
                if(textStatus == "success"){
                    self.trigger(settings.actions.watchlistItemRemove.eventSuccessName, [data, textStatus, url, itemId, item]);
                }else{
                    self.trigger(settings.actions.watchlistItemRemove.eventErrorName, [data, textStatus, url, itemId, item]);
                }
            });
        });

        doc.on(settings.actions.watchlistItemRemove.eventSuccessName, function(e, data, textStatus, url, itemId, item){
            var selector = '[data-'+ settings.actions.watchlistItemRemove.dataContainerRemoveSelect +']';
            self.log('remove closest to item container from dom: '+ selector);
            item.closest(selector).remove();
        });
    };

    /**
     * Strategy Form Handler
     * @param jQuery form
     */
    this.registerStrategyServiceFormHandler = function(form){
        self.log('syliusShop.registerStrategyFormHandler');

        var strategies = form.find('[data-strategies]');

        strategies
            .find('[data-strategy] [data-parent]:visible :input:not(:checked)')
            .closest('[data-strategy]')
            .find('[data-child]')
            .hide();

        form.find(':submit').click(function(e){
            self.log('StrategyChoice Form submit -> disable all unneeded inputs');
            strategies
                .find('[data-strategy] [data-parent] :input:not(:checked)')
                .closest('[data-strategy]')
                .find('[data-child] :input')
                .prop('disabled', 'disabled');
        });

        var strategieChoiceParentInputs = strategies.find('[data-strategy] [data-parent] :input');

        strategieChoiceParentInputs.change(function(){
            self.log('StrategyChoice Parent Input changed');
            strategieChoiceParentInputs.each(function(){
                var elem = $(this);

                var elemIsChecked = elem.is(':checked');

                var childForm = elem.closest('[data-strategy]').find('[data-child]');

                if(elemIsChecked || elem.closest('[data-parent]').is(':hidden')){
                    childForm.show();
                }else{
                    childForm.hide();
                }

                if(!elemIsChecked){
                    var inputs = childForm.find(':input');
                    childForm.find(':input').prop('checked', false);
                }
            });
        });

        strategies.find('[data-strategy] [data-child] :input').change(function(){
            self.log('StrategyChoice Child changed');
            $(this)
                .closest('[data-strategy]')
                .find('[data-parent] :input')
                .prop('checked', 'checked')
                .change();
        });
    };

    /**
     * Invoice Same As Delivery Handler
     */
    this.registerInvoiceSameAsDeliveryHandler = function(){
        self.log('syliusShop.registerInvoiceSameAsDeliveryHandler');

        var addressDelivery = $('[data-deliveryaddress]');
        var addressDeliveryInputs = addressDelivery.find(':input');

        if(parseInt($('[data-invoicesameasdelivery] input:checked').val()) == 1){
            addressDeliveryInputs.prop("disabled", "disabled");
            addressDelivery.hide();
        }else{
            addressDeliveryInputs.removeProp("disabled");
        }

        $('[data-invoicesameasdelivery] input').click(function(e){
            if(parseInt($('[data-invoicesameasdelivery] input:checked').val()) == 1){
                addressDeliveryInputs.prop("disabled", "disabled");
                addressDelivery.slideUp();
            }else{
                addressDeliveryInputs.removeProp("disabled");
                addressDelivery.slideDown();
            }
        });
    };

    var self = window.syliusShop = this;
}(jQuery, document, window, console, hinclude));