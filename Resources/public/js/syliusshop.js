function syliusshop_registerCartEvents(cartContainerId, defaultQuantity){
    var doc = $(document);

    defaultQuantity = parseInt(defaultQuantity);
    if(defaultQuantity <= 0 || !defaultQuantity){
        defaultQuantity = 1;
    }

    doc.on('syliusshop.cart.changed', function(){
        if(typeof hinclude != 'undefined'){
            hinclude.refresh(cartContainerId);
        }
    });

    doc.on('click', '[data-item-add-action]', function(e){
        e.preventDefault();

        var item = $(this);

        $.post(item.attr('href'), {
            itemId: item.data('item-add-action'),
            quantity: syliusshop_getItemQuantity(item, defaultQuantity)
        }, function(data, textStatus){
            var eventName = textStatus == 'success' ? 'syliusshop.cart.changed' : 'syliusshop.cart.item.error';
            doc.trigger(eventName, [data, textStatus]);
        });
    });
}

function syliusshop_registerInvoiceSameAsDelivery()
{
    var addressDelivery = $('[data-deliveryaddress]');
    var addressDeliveryInputs = addressDelivery.find(':input');

    if($('[data-invoicesameasdelivery] input:checked').val() == "1"){
        addressDeliveryInputs.prop("disabled", "disabled");
        addressDelivery.hide();
    }else{
        addressDeliveryInputs.removeProp("disabled");
    }

    $('[data-invoicesameasdelivery] input').click(function(e){
        if($('[data-invoicesameasdelivery] input:checked').val() == "1"){
            addressDeliveryInputs.prop("disabled", "disabled");
            addressDelivery.slideUp();
        }else{
            addressDeliveryInputs.removeProp("disabled");
            addressDelivery.slideDown();
        }
    });
}

function syliusshop_registerStrategyServiceForm(form){
    var strategies = form.find('[data-strategies]');

    strategies
        .find('[data-parent]:visible :input:not(:checked)')
        .closest('[data-strategy]')
        .find('[data-child]')
        .hide();

    form.find(':submit').click(function(e){
        strategies
            .find('[data-parent] :input:not(:checked)')
            .closest('[data-strategy]')
            .find('[data-child] :input')
            .prop('disabled', 'disabled');
    });

    var strategieChoices = strategies.find('[data-parent] :input');

    strategieChoices.change(function(){
        strategieChoices.each(function(){
            var elem = $(this);

            var elemIsChecked = elem.is(':checked');
            var childForm = elem.closest('[data-strategy]').find('[data-child]');

            if(elemIsChecked || elem.closest('[data-parent]').is(':hidden')){
                childForm.show();
            }else{
                childForm.hide();
            }

            if(!elemIsChecked){
                childForm.find(':input').prop('checked', false);
            }
        });
    });

    strategies.find('[data-strategy] [data-child] :input').change(function(){
        $(this)
            .closest('[data-strategy]')
            .find('[data-parent] :input')
            .removeProp('checked')
            .change();
    });
}

function syliusshop_getItemQuantity(item, defaultQuantity){
    var itemActionQuantity = parseInt(item.data('item-add-quantity'));
    if(itemActionQuantity > 1){
        return itemActionQuantity;
    }

    var itemInputQuantity = parseInt(item.closest('[data-item-add-container]').find('[data-item-add-quantity]').val());
    if(itemInputQuantity > 1){
        return itemInputQuantity;
    }

    return defaultQuantity;
}