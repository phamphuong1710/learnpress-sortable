<?php
/**
 * Template for displaying answer of fill-in-blank question.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/fill-in-blank/content-question/answer.php.
 *
 * @author   ThimPress
 * @package  LearnPress/Fill-In-Blank/Templates
 * @version  3.0.1
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

isset( $question ) or die( __( 'Invalid question!', 'htc-sortable' ) );
$user = LP_Global::user();
$quiz = LP_Global::course_item_quiz();

if ( ! $answers = $question->get_answers() ) {
	return;
}

$question->setup_data( $quiz->get_id() );
$list_sort = array();
foreach ( $answers as $key => $answer ) {
	$list_sort[ $answer['answer_order'] ] = $answer['sort'];
}
$class = '';

shuffle( $list_sort );

$answered = $question->get_answered();

?>

<div class="question-type-sortable">
	<div class="question-options-<?php echo $question->get_id(); ?> question-passage">
		
		<?php if ( ! $user->has_completed_quiz( $quiz->get_id() ) && ! $user->has_checked_question( $question->get_id(), $quiz->get_id() ) ): ?>
			<div class="list-option-answer-sort">
				<div class="sort-elements-wrapper">
					<div class="htc-data--answer sort-elements">
						<?php foreach ( $list_sort as $key => $sort ) : ?>

							<div class="sortable-ui-value" data-sort-item="<?php echo esc_html( $sort ); ?>">

								<div class="sort-value">
									<span class="sort-anser-value">
										<?php echo esc_html( $sort ); ?>
									</span>
								</div>
							</div>

						<?php endforeach ?>
					</div>
				</div>
			</div>
		<?php endif ?>

		<div class="list-option-answer-sortable">

			<?php
				foreach ( $answers as $k => $answer ) {
					$position = (int) $answer['answer_order'];
					if ( $user->has_completed_quiz( $quiz->get_id() ) || $user->has_checked_question( $question->get_id(), $quiz->get_id() ) ) {
						if ( $answer['sort'] == $answered[$position] ) {
							$class = 'correct';
						} else {
							$class = 'incorrect';
						}
					}
					?>
					<div class="item-option-ans <?php echo esc_attr( $class ); ?>">
						<div class="option-answer">
							<span class="lable"><?php echo esc_html( $answer->get_title( 'display' ) ); ?></span>
						</div>
						<div class="option-sort-answer <?php echo esc_attr( $class ); ?>">

							<?php if ( $user->has_completed_quiz( $quiz->get_id() ) || $user->has_checked_question( $question->get_id(), $quiz->get_id() ) ) { ?>
								<div class="blank--sort">
									<div class="sortable-ui-value">
										<?php if ( $class == 'correct' ): ?>
											<span class="sort-value">
												<?php echo esc_html( $answer['sort'] ); ?>
											</span>
										<?php endif ?>
										<?php if ( $class == 'incorrect' ): ?>
											<span class="sort-value ">
												<span class="user-answer">
													<?php echo esc_html( $answered[$position] ); ?>
												</span>
												<span class="correct-answer">
													<?php echo esc_html( $answer['sort'] ); ?>
												</span>
												
											</span>
										<?php endif ?>

									</div>
								</div>

							<?php } else { ?>
							<div class="sortable-ui-value htc-data--blank blank--sort">
							</div>
								<input type="hidden" name="learn-press-question-<?php echo $question->get_id(); ?>[<?php echo $answer['answer_order']; ?>]"
									value=""
									class="answer-options"/>

							<?php } ?>
						</div>

					</div>


					<?php
					do_action( 'learn_press_after_question_answer_text', $answer, $question );
				}
			?>
			</div>
	</div>
</div>
