<?php
/**
 * Admin question editor: question answer template.
 *
 * @since 1.0.0
 */
?>

<script type="text/x-template" id="tmpl-lp-question-answer-sortable-option">
    <tr class="answer-option" :class="[isNew() ? 'empty-option' : '']" :data-answer-id="answer.question_answer_id"
        :data-order-answer="index">
        <td class="sort lp-sortable-handle"><?php learn_press_admin_view( 'svg-icon' ); ?></td>
        <td class="order">{{index +1}}.</td>
        <td class="answer-text">
            <input type="text" v-model="answer.text"
                   @change="changeTitle" @keyup.enter="updateTitle" @blur="updateTitle"/>
        </td>
        <td class="answer-text">

             <input type="text" :value="answer.sort"
                    class="sort-answer">
        </td>
        <td class="actions lp-toolbar-buttons">
            <div class="lp-toolbar-btn lp-btn-remove remove-answer" v-if="deletable">
                <a class="lp-btn-icon dashicons dashicons-trash" @click="deleteAnswer"></a>
            </div>
        </td>
    </tr>
</script>

<script type="text/javascript">
    jQuery(function ($) {
        var $store = window.LP_Quiz_Store;
        window.$Vue = window.$Vue || Vue;

        $Vue.component('lp-quiz-sortable-answer-option', {
            template: '#tmpl-lp-question-answer-sortable-option',
            props: ['question', 'answer', 'index'],
            data: function () {
                return {
                    changed: false
                }
            },
            computed: {
                // answer id
                id: function () {
                    return this.answer.question_answer_id;
                },
                // check correct answer
                correct: function () {
                    return this.answer.is_true === 'yes';
                },
                // input correct form name
                name: function () {
                    return 'answer_question[' + $store.getters['id'] + '][' + this.index + ']';
                },
                // deletable answer
                deletable: function () {
                    return !(this.number < 1 || (this.correct && $store.getters['numberCorrect'] === 1) );
                }
            },
            mounted: function () {
                if (this.isNew()) {
                    this.changed = true;
                    this.updateTitle();
                }
            },
            methods: {
                changeTitle: function () {
                    this.changed = true;
                },
                updateTitle: function () {
                    if (this.changed) {
                        this.$emit('updateTitle', this.answer);
                    }
                },
                changeCorrect: function (e) {
                    this.answer.is_true = (e.target.checked) ? 'yes' : '';
                    this.$emit('changeCorrect', this.answer);
                },
                deleteAnswer: function () {
                    this.$emit('deleteAnswer', {
                        id: this.id,
                        order: this.answer.answer_order
                    });
                },
                isNew: function () {
                    return isNaN(this.answer.question_answer_id);
                }
            }
        })
    });

</script>
