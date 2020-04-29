<?php
/**
 * LearnPress Fill In Blank Functions
 *
 * Define common functions for both front-end and back-end
 *
 * @author   ThimPress
 * @package  LearnPress/Fill-In-Blank/Functions
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'learn_press_sortable_admin_view' ) ) {

	/**
	 * Get admin view file.
	 *
	 * @param        $view
	 * @param string $args
	 */
	function learn_press_sortable_admin_view( $view, $args = array() ) {
		learn_press_admin_view( $view, wp_parse_args( $args, array( 'plugin_file' => LP_ADDON_SORTABLE_FILE ) ) );
	}
}

if ( ! function_exists( 'learn_press_sortable_get_template' ) ) {
	/**
	 * Get template.
	 *
	 * @param       $template_name
	 * @param array $args
	 */
	function learn_press_sortable_get_template( $template_name, $args = array() ) {
		learn_press_get_template( $template_name, $args, learn_press_template_path() . '/addons/sortable/', LP_ADDON_SORTABLE_PATH . '/templates/' );
	}
}

/**
 * Filter to admin editor of quiz/question to add new data.
 *
 * @param array $answers
 * @param int   $question_id
 * @param int   $quiz_id
 *
 * @return mixed
 */
function learn_press_sortable_admin_editor_question_answers( $answers, $question_id, $quiz_id ) {

	if ( ! $question = learn_press_get_question( $question_id ) ) {
		return $answers;
	}

	if ( 'sortable' !== $question->get_type() ) {
		return $answers;
	}

	if ( $answers ) {
		foreach ( $answers as $k => $answer ) {
			$blanks                  = learn_press_get_question_answer_meta( $answer['question_answer_id'], '_blanks', true );
			$answers[ $k ]['blanks'] = $blanks ? array_values( $blanks ) : array();
		}
	}

	return $answers;
}

add_filter( 'learn-press/quiz-editor/question-answers-data', 'learn_press_sortable_admin_editor_question_answers', 10, 3 );
add_filter( 'learn-press/question-editor/question-answers-data', 'learn_press_sortable_admin_editor_question_answers', 10, 3 );

/**
 * Add new translating
 *
 * @param array $i18n
 *
 * @return mixed
 */
function learn_press_sortable_admin_editor_i18n( $i18n ) {
	$i18n['confirm_remove_blanks'] = __( 'Are you sure to remove all blanks?', 'htc-sortable' );

	return $i18n;
}

add_filter( 'learn-press/question-editor/i18n', 'learn_press_sortable_admin_editor_i18n' );
add_filter( 'learn-press/quiz-editor/i18n', 'learn_press_sortable_admin_editor_i18n' );

/**
 * Backward compatibility. Will remove in learnpress 3.0.9
 */
function learn_press_sortable_admin_editor_data_backward( $data ) {
	if ( current_filter() === 'learn-press/admin-localize-quiz-editor' ) {
		if ( ! empty( $data['listQuestions'] ) ) {
			if ( ! empty( $data['listQuestions']['questions'] ) ) {
				foreach ( $data['listQuestions']['questions'] as $k => $question ) {
					if ( $answers = $data['listQuestions']['questions'][ $k ]['answers'] ) {
						$data['listQuestions']['questions'][ $k ]['answers'] = learn_press_sortable_admin_editor_question_answers( $answers, $question['id'], 0 );
					}
				}
			}
		}
	} elseif ( current_filter() === 'learn-press/question-editor/localize-script' ) {
		if ( $answers = $data['root']['answers'] ) {
			$data['root']['answers'] = learn_press_sortable_admin_editor_question_answers( $answers, $data['root']['id'], 0 );
		}
	}
	$data['i18n'] = learn_press_sortable_admin_editor_i18n( $data['i18n'] );

	return $data;
}

add_filter( 'learn-press/question-editor/localize-script', 'learn_press_sortable_admin_editor_data_backward' );
add_filter( 'learn-press/admin-localize-quiz-editor', 'learn_press_sortable_admin_editor_data_backward' );

function htc_sortable_get_answer_by_question( $question_id ) {
	global $wpdb;
	$list_answer = $wpdb->get_results( "SELECT * FROM $wpdb->learnpress_question_answers  WHERE question_id = " . $question_id . " ORDER BY answer_order ASC" );
	foreach ( $list_answer as $answer ) {
		$data = unserialize( $answer->answer_data );
		if ( ! empty( $data ) ) {
			$lable = $data['text'];
			$sort = $data['sort'];
		}

		?>
		<tr class="answer-option" data-answer-id="<?php echo esc_attr( $answer->question_answer_id ); ?>"  data-postion="<?php echo esc_attr( $answer->answer_order ); ?>">
				<td class="sort lp-sortable-handle"><?php learn_press_admin_view( 'svg-icon' ); ?></td>
				<td class="order"><?php echo esc_html( $answer->answer_order ); ?></td>
				<td class="answer-text">
					<form >
						<input type="text" value="<?php echo esc_html( $lable ); ?>" class="label">
					</form>
				</td>
				<td class="answer-text">
					<input type="text" value="<?php echo esc_html( $sort ); ?>" class="sort-answer">
				</td>
				<td class="actions lp-toolbar-buttons">
					<div class="lp-toolbar-btn lp-btn-remove remove-answer">
						<a class="lp-btn-icon dashicons dashicons-trash"></a>
					</div>
				</td>
		</tr>
		<?php
	}
}

function htcsortable_get_quiz_by_id( $quiz_id ) {

}

