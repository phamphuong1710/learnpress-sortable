;(function ($) {
    "use strict";

    function _ready() {
        (typeof LearnPress !== 'undefined') && LearnPress.Hook.addAction('learn_press_check_question', function (response, that) {
            if (!response || response.result !== 'success') {
                return;
            }
            var $current = that.model.current(),
                $content = $($current.get('content'));
            $content.find('.question-passage').replaceWith(response.checked);
            $content.addClass('checked').find('input, select, textarea').prop('disabled', true);
            $current.set('content', $content);
        });

        $( ".htc-data--blank, .htc-data--answer" ).sortable({
            cursor: "move",
            connectWith: ".htc-data--blank:not(:has(span)), .htc-data--answer",//.sortable-ui-value
            placeholder: 'htc-sortable-placeholder',
            stop: function( event, ui ) {
                
                var answers = $(this);
                var classes = answers.hasClass( 'blank--sort' );
                if ( classes ) {
                    var input = answers.parents('.option-sort-answer ').find('.answer-options');
                    input.val('');
                }
            },
            update: function(event, ui) {
                var result = $(this).sortable('toArray', {attribute: 'data-sort-item'});
                var that =  $(this);
                var sortValue = that.hasClass( '.blank--sort' );
                $.each(result, function(index, value){
                    var option = that.parent('.option-sort-answer');
                    var valInput = option.find('.answer-options');
                    valInput.val(value);
                });
            },
        }).disableSelection();
    }

    $(document).ready(_ready);
})(jQuery);