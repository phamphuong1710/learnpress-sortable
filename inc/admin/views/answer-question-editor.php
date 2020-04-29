<?php
/**
 * Admin question editor: sortable question answer template.
 *
 * @since 3.0.0
 */
learn_press_admin_view( 'question/actions' );
learn_press_sortable_admin_view( 'options' );
$content_id = uniqid( 'sortable-content-' );
$question_id = get_the_ID();
?>

<script type="text/x-template" id="tmpl-lp-sortable-question-answer">

	<div id="lp-admin-question-editor" class="admin-quiz-sortable-question-editor">
		<?php //learn_press_sortable_admin_view( 'answer-editor' ); ?>
			<div class="lp-box-data-content admin-sortable-question-editor">
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
								<?php htc_sortable_get_answer_by_question( $question_id ); ?>
							</tbody>
						</table>
						<p class="add-answer">
							<button class="button add-sortable-question-option-button" type="button" ><?php esc_html_e( 'Add option', 'htc-softable' ); ?></button>
						</p>
					</div>
				</div>
			</div>
	</div>
</script>

<script type="text/javascript">

	jQuery(document).ready(function ($) {
		var $Vue = window.$Vue || Vue;
		var $store = window.LP_Question_Store;
		Vue.component('lp-sortable-question-answer', {
			template: '#tmpl-lp-sortable-question-answer',
			props: ['type', 'answers', 'rawBlanks'],
		})
	});

</script>
