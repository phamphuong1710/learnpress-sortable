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


				},
			}
		});
	});
</script>
