/**
 * @package     Gamuza_Basic
 * @copyright   Copyright (c) 2023 Gamuza Technologies. (https://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

$j(document).ready(function(){
    /**
     * Bootstrap
     */
    $j('a.button, button.button').removeClass('button').addClass('btn btn-success');
    $j('a.checkout-button, button.btn-checkout').removeClass('checkout-button btn-checkout').addClass('btn-lg');
    $j('input.input-text').removeClass('input-text').addClass('form-control');
    $j('input.checkbox:checkbox').addClass('form-check-input');
    $j('input.radio:radio').addClass('form-check-input');
    $j('select').addClass('form-select');
    $j('textarea.input-text').removeClass('input-text').addClass('form-control');

    /**
     * TinySlider
     */
    $j('.products-slider').each(function(index, value){
        var slider = tns({
            container: value,
            items: 2,
            gutter: 10,
            controls: false,
            nav: false,
            speed: 1000,
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            responsive: {
                481: {
                    items: 5,
                }
            }
        });
    });
});

