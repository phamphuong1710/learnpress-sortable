<?php
/**
 * Admin quiz editor: fib question answer template.
 *
 * @since 3.0.0
 */
learn_press_sortable_admin_view( 'quizz-options' );

?>

<script type="text/x-template" id="tmpl-lp-quiz-sortable-question-answer">
	<div class="admin-editor-lp_question">
		<div class="admin-quiz-sortable-question-editor admin-sortable-question-editor" >
			<div class="lp-box-data-content">
				<div class="learn-press-question quiz-question-data">
					<div class="description lp-list-questions">
						<table class="lp-list-options list-question-answers">
							<thead>
								<tr>
									<th class="sort"></th>
									<th class="order">#</th>
									<th class="answer-text"><?php _e( 'Label', 'htc-softable' ); ?></th>
									<th class="answer-text"><?php _e( 'Sort', 'htc-softable' ); ?></th>
									<th class="actions"></th>
								</tr>
							</thead>
							<tbody class="ui-sortable">
								<lp-quiz-sortable-answer-option v-for="(answer, index) in question.answers" :question="question" :answer="answer" :index="index" :key="index"></lp-quiz-sortable-answer-option>
							</tbody>
						</table>
						<p class="add-answer">
							<button class="button add-sortable-question-option-button" type="button" @click="newAnswer" ><?php esc_html_e( 'Add option', 'htc-softable' ); ?></button>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/javascript">

	jQuery(document).ready(function ($) {
		var $Vue = window.$Vue || Vue;
		var $store = window.LP_Quiz_Store;
		Vue.component('lp-quiz-sortable-question-answer', {
			template: '#tmpl-lp-quiz-sortable-question-answer',
			props: ['question'],

			mounted: function () {
				var _self = this;
				//setTimeout(function () {
					var $el = $(_self.$el).find('.lp-list-options tbody');
					console.log( $el );
					$el.sortable({
						handle: '.sort',
						axis: 'y',
						helper: function (e, ui) {
							var $tr = $('<tr />'),
								$row = $(e.target).closest('tr');
							$row.children().each(function () {
								var $td = $(this).clone().width($(this).width())
								$tr.append($td);
							});

							return $tr;
						},
						update: function () {
							_self.sort();
						}
					});
				//}, 1000)

			},
			methods: {

				// new answer option
				newAnswer: function () {
					// $store.dispatch('lqs/newQuestionAnswer', {
					// 	question_id: this.question.id, success: function (answer) {
					// 		$(this.$el).find('tr[data-answer-id="' + answer.question_answer_id + '"] .answer-text input').focus();
					// 	}, context: this
					// });


				},
			}
		});
	});
</script>
<!-- <script type="text/x-template" id="tmpl-lp-quiz-answer-sortable-option">
	<tr class="answer-option" :class="[isNew() ? 'empty-option' : '']" :data-answer-id="answer.question_answer_id"
		:data-order-answer="index">
		<td class="sort lp-sortable-handle"><?php learn_press_admin_view( 'svg-icon' ); ?></td>
		<td class="order">{{index +1}}.</td>
		<td class="answer-text">
			<input type="text" v-model="answer.text"
				   @change="changeTitle" @keyup.enter="updateTitle" @blur="updateTitle" @keyup="keyUp"/>
		</td>
		<td class="answer-correct lp-answer-check">

 
		</td>
		<td class="actions lp-toolbar-buttons">
			<div class="lp-toolbar-btn lp-btn-remove" v-if="deletable">
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
			template: '#tmpl-lp-quiz-answer-sortable-option',
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

</script> -->