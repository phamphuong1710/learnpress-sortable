<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Fill-In-Blank/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Sortable' ) ) {

	/**
	 * Class LP_Addon_Sortable
	 */
	class LP_Addon_Sortable extends LP_Addon {

		/**
		 * @var string
		 */
		public $version = LP_ADDON_SORTABLE_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_SORTABLE_REQUIRE_VER;

		/**
		 * @var LP_FIB_Upgrade
		 */
		public $upgrader = null;

		/**
		 * LP_Addon_Sortable constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->_maybe_upgrade_data();
			$tool_path = dirname( LP_ADDON_FILL_IN_BLANK_FILE ) . "/inc/admin/class-upgrade-database.php";
			if ( file_exists( $tool_path ) ) {
				$this->upgrader = include_once( $tool_path );
			}
			add_action( 'learn-press/question/updated-answer-data', array(
				$this,
				'update_question_answer_meta'
			), 10, 3 );

			// delete answer meta before delete question.
			add_action( 'learn-press/before-clear-question', array( $this, 'clear_question_answer_meta' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_style' ) );

			add_action( 'wp_ajax_htc_new_answer_option', array( $this, 'add_answer_row' ) );
			add_action( 'wp_ajax_nopriv_htc_new_answer_option', array( $this, 'add_answer_row' ) );

			add_action( 'wp_ajax_htc_all_answer_option', array( $this, 'all_answer_option' ) );
			add_action( 'wp_ajax_nopriv_htc_all_answer_option', array( $this, 'all_answer_option' ) );

			add_action( 'wp_ajax_htc_update_answer_position', array( $this, 'update_answer_option' ) );
			add_action( 'wp_ajax_nopriv_htc_update_answer_position', array( $this, 'update_answer_option' ) );

			add_action( 'wp_ajax_htc_delete_answer_option', array( $this, 'delete_answer_option' ) );
			add_action( 'wp_ajax_nopriv_htc_delete_answer_option', array( $this, 'delete_answer_option' ) );

			add_action( 'wp_ajax_htc_update_answer_label', array( $this, 'update_answer_label' ) );
			add_action( 'wp_ajax_nopriv_htc_update_answer_label', array( $this, 'update_answer_label' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'sortable_js' ), 99 );
		}

		/**
		 * Update needed answer meta.
		 *
		 * @param int   $question_id
		 * @param int   $answer_id
		 * @param mixed $answer_data
		 */
		public function update_question_answer_meta( $question_id, $answer_id, $answer_data ) {
			if ( ! empty( $answer_data['blanks'] ) ) {
				$blanks = $answer_data['blanks'];
			} else {
				$blanks = '';
			}

			if ( is_array( $blanks ) ) {
				/**
				 * @var $question LP_Question_Fill_In_Blank
				 */
				$question = LP_Question::get_question( $question_id );
				foreach ( $blanks as $id => $blank ) {
					$question->_blanks[ $blank['id'] ] = $blank;
				}

			}

			learn_press_update_question_answer_meta( $answer_id, '_blanks', $blanks );

		}

		/**
		 * Delete answer meta before delete FIB question.
		 *
		 * @param $question_id
		 */
		public function clear_question_answer_meta( $question_id ) {
			$question = LP_Question::get_question( $question_id );
			$answers  = $question->get_answers();

			foreach ( $answers as $answer_id ) {
				learn_press_delete_question_answer_meta( $answer_id, '_blanks', '', true );
			}
		}

		protected function _get_questions() {
			global $wpdb;

			$query = $wpdb->prepare( "
				SELECT p.ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm  ON p.ID = pm.post_id AND pm.meta_key = %s AND pm.meta_value = %s
				LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = %s
				WHERE pm2.meta_value IS NULL OR pm2.meta_value <> %s
			", '_lp_type', 'sortable', '_db_version', '3.0.0' );

			return $wpdb->get_col( $query );

		}

		protected function _maybe_upgrade_data() {

			if ( version_compare( LP_ADDON_SORTABLE_VER, '3.0.0', '=' ) && version_compare( get_option( 'learnpress_sortable_db_version' ), '3.0.0', '<' ) ) {

				global $wpdb;

				if ( ! $question_ids = $this->_get_questions() ) {
					return;
				}

				$format = array_fill( 0, sizeof( $question_ids ), '%d' );
				$args   = array_merge( array(
					'_lp_type',
					'sortable',
				), $question_ids );


				$query = $wpdb->prepare( "
					SELECT qa.*
					FROM {$wpdb->learnpress_question_answers} qa
					INNER JOIN {$wpdb->learnpress_quiz_questions} qq ON qq.question_id = qa.question_id
					INNER JOIN {$wpdb->postmeta} pm ON pm.meta_key = %s AND pm.meta_value = %s AND pm.post_id = qq.question_id
					AND qq.question_id IN(" . join( ',', $format ) . ")
					LIMIT 0, 100
				", $args );

				if ( ! $answers = $wpdb->get_results( $query ) ) {
					return;
				}

				$queue_items = array();
				foreach ( $answers as $answer ) {
					$answer_data = maybe_unserialize( $answer->answer_data );

					if ( array_key_exists( 'text', $answer_data ) ) {
						continue;
					}

					$answer_text = reset( $answer_data );

					$answer_data = array(
						'text'    => $answer_text,
						'value'   => learn_press_uniqid(),
						'is_true' => ''
					);

					if ( empty( $queue_items[ $answer->question_id ] ) ) {
						$queue_items[ $answer->question_id ] = array();
					}

					$queue_items[ $answer->question_id ][] = array(
						'question_answer_id' => $answer->question_answer_id,
						'answer_data'        => $answer_data
					);
				}

				if ( $queue_items ) {
					LP_Background_Global::add( 'update-sortable-answers', array( 'questions' => $queue_items ), array(
						$this,
						'upgrade_db'
					) );
				}
			}
		}

		/**
		 * Upgrade database
		 *
		 * @param array $questions
		 */
		public function upgrade_db( $questions ) {
			global $wpdb;

			if ( ! $questions ) {
				return;
			}

			foreach ( $questions as $question_id => $answers ) {
				$updated = 0;
				foreach ( $answers as $answer ) {
					if ( $wpdb->update(
						$wpdb->learnpress_question_answers,
						array(
							'answer_data' => maybe_serialize( $answer['answer_data'] )
						),
						array(
							'question_answer_id' => $answer['question_answer_id'],
							'question_id'        => $question_id
						),
						array( '%s' ),
						array( '%d', '%d' )
					)
					) {
						$updated ++;
					}
				}

				if ( $updated ) {
					update_post_meta( $question_id, '_db_version', LP_ADDON_SORTABLE_VER );
				}
			}
		}

		/**
		 * Define Learnpress Sortable constants.
		 *
		 * @since 3.0.0
		 */
		protected function _define_constants() {
			if ( ! defined( 'LP_ADDON_SORTABLE_PATH' ) ) {
				define( 'LP_ADDON_SORTABLE_PATH', dirname( LP_ADDON_SORTABLE_FILE ) );
				define( 'LP_ADDON_SORTABLE_ASSETS', LP_ADDON_SORTABLE_PATH . '/assets/' );
				define( 'LP_ADDON_SORTABLE_INC', LP_ADDON_SORTABLE_PATH . '/inc/' );
				define( 'LP_ADDON_SORTABLE_TEMPLATE', LP_ADDON_SORTABLE_PATH . '/templates/' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		protected function _includes() {
			include_once LP_ADDON_SORTABLE_INC . 'class-lp-question-sortable.php';
			include_once LP_ADDON_SORTABLE_INC . 'functions.php';
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 3.0.0
		 */
		protected function _init_hooks() {
			add_filter( 'learn_press_question_types', array( __CLASS__, 'register_question' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_filter( 'learn-press/admin/external-js-component', array( $this, 'add_external_component_type' ) );

			// js template for admin editor
			add_action( 'edit_form_after_editor', array( $this, 'js_template' ) );
			// add vue component tag to quiz, question editor
			add_action( 'learn-press/question-editor/question-js-component', array( $this, 'question_component' ) );
			add_action( 'learn-press/quiz-editor/question-js-component', array( $this, 'quiz_question_component' ) );
		}

		/**
		 * Enqueue assets.
		 *
		 * @since 3.0.0
		 */
		public function enqueue_scripts() {
			if ( is_admin() ) {
				$assets = learn_press_admin_assets();
				$assets->enqueue_style( 'lp-sortable-question-admin-css', $this->get_plugin_url( 'assets/css/admin.sortable.css' ) );
				$assets->enqueue_script( 'sortable-js', $this->get_plugin_url( 'assets/js/admin.sortable.js' ), array( 'jquery', 'jquery-ui-sortable' ) );
				$admin_vars = array(
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'htc_editer_nonce' ),
					'edit'  => admin_url( 'edit.php?post_type=btf_builder' ),
				);

				wp_localize_script(
					'sortable-js',
					'admin',
					$admin_vars
				);
			} else {
				$assets = learn_press_assets();
				$assets->enqueue_script( 'lp-sortable-question-js', $this->get_plugin_url( 'assets/js/sortable.js' ), array( 'jquery' ) );
				$assets->enqueue_style( 'lp-sortable-question-css', $this->get_plugin_url( 'assets/css/sortable.css' ) );
			}
		}

		/**
		 * Register question to Learnpress list question types.
		 *
		 * @since 3.0.0
		 *
		 * @param $types
		 *
		 * @return mixed
		 */
		public static function register_question( $types ) {
			$types['sortable'] = __( 'Sortable', 'htc-sortable' );

			return $types;
		}

		/**
		 * Sortable question JS Template for admin quiz and question.
		 */
		public function js_template() {
			if ( get_post_type() == LP_QUESTION_CPT ) {
				learn_press_sortable_admin_view( 'answer-question-editor' );
			} else if ( get_post_type() == LP_QUIZ_CPT ) {
				learn_press_sortable_admin_view( 'answer-quiz-editor' );
			}
		}

		/**
		 * Add questions type has js external component.
		 *
		 * @param $types
		 *
		 * @return array
		 */
		public function add_external_component_type( $types ) {
			$types[] = 'sortable';

			return $types;
		}

		/**
		 * Add Vue component to admin question editor.
		 */
		public function question_component() { ?>
			<lp-sortable-question-answer v-if="type=='sortable'" :type="type" :answers="answers"></lp-sortable-question-answer>
		<?php }

		/**
		 * Add Vue component to admin quiz editor.
		 */
		public function quiz_question_component() { ?>
			<lp-quiz-sortable-question-answer v-if="question.type.key == 'sortable'" :question="question"></lp-quiz-sortable-question-answer>
		<?php }

		public function load_admin_style() {
			wp_enqueue_script(
				'htc-editer-quiz',
				$this->get_plugin_url( 'assets/js/anser-quiz-editor.js' ),
				array( 'jquery', 'jquery-ui-sortable' ),
				LP_ADDON_SORTABLE_VER,
				true
			);
			$admin_vars = array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'htc_editer_nonce' ),
			);

			wp_localize_script(
				'htc-editer-quiz',
				'admin',
				$admin_vars
			);
		}

		public function add_answer_row () {
			check_ajax_referer( 'htc_editer_nonce' );
			$question_id = $_GET['question_id'];
			$index       = (int) $_GET['index'];
			global $wpdb;
			if( $index === 0 ) {
				$wpdb->delete(
					$wpdb->learnpress_question_answers,
					array(
						'answer_order' => 1,
						'question_id' => $question_id,
					)
				);
			}
			$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->learnpress_question_answers WHERE question_id = " . $question_id );

			$index = (int)$rowcount + 1;

			$data = array(
				'text' => 'New answers',
				'sort' => 'Sort Label',
				'position' => $index,
			);
			$wpdb->insert(
				$wpdb->learnpress_question_answers,
				array(
					'question_id'  => $question_id,
					'answer_data'  => serialize( $data ),
					'answer_order' => $index,
				),
				array( '%d', '%s', '%d' )
			);
			$question_answer_id = $wpdb->insert_id;
			if ( $question_answer_id ) {
				?>
				<tr class="answer-option" data-answer-id="<?php echo esc_attr( $question_answer_id ); ?>" data-postion="<?php echo esc_attr( $index ); ?>">
						<td class="sort lp-sortable-handle"><?php learn_press_admin_view( 'svg-icon' ); ?></td>
						<td class="order"><?php echo esc_html( $index . '.' ); ?></td>
						<td class="answer-text">
							<form >
								<input type="text" value="<?php echo esc_attr__( 'New answers', 'htc-sortable' ); ?>" class="label">
							</form>
						</td>
						<td class="answer-text">
							<input type="text" value="<?php echo esc_attr__( 'Sort label', 'htc-sortable' ); ?>" class="sort-answer">
						</td>
						<td class="actions lp-toolbar-buttons">
							<div class="lp-toolbar-btn lp-btn-remove remove-answer">
								<a class="lp-btn-icon dashicons dashicons-trash"></a>
							</div>
						</td>
				</tr>
				<?php
			} else {
				return false;
			}

			die();
		}

		public function all_answer_option() {
			check_ajax_referer( 'htc_editer_nonce' );
			$question_id = $_GET['question_id'];
			$lable = '';
			$sort = '';
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
				// die();
			}
		}

		public function update_answer_option() {
			check_ajax_referer( 'htc_editer_nonce' );
			$answer_id = (int) $_POST['answer_id'];
			$position = (int) $_POST['postion'];
			global $wpdb;
			$wpdb->update(
				$wpdb->learnpress_question_answers,
				array(
					'answer_order' => $position,
				),
				array( 'question_answer_id' => $answer_id )
			);
			wp_send_json_success( 'Answer update Success!' );

			die();
		}

		public function delete_answer_option() {
			check_ajax_referer( 'htc_editer_nonce' );
			$answer_id = (int) $_POST['answer_id'];
			$question_id = (int) $_POST['question_id'];
			global $wpdb;
			$wpdb->delete(
				$wpdb->learnpress_question_answers,
				array( 'question_answer_id' => $answer_id )
			);
			$list_answer = $wpdb->get_results( "SELECT * FROM $wpdb->learnpress_question_answers  WHERE question_id = " . $question_id . " ORDER BY answer_order ASC" );
			$position = 1;
			foreach ( $list_answer as $key => $answer ) {
				$wpdb->update(
					$wpdb->learnpress_question_answers,
					array(
						'answer_order' => $position,
					),
					array( 'question_answer_id' => $answer->question_answer_id )
				);

				$position++;
			}
			wp_send_json_success( 'Answer Delete Success!' );

			die();
		}


		public function update_answer_label() {
			check_ajax_referer( 'htc_editer_nonce' );
			global $wpdb;
			$answer_id = (int) $_POST['answer_id'];
			$label = $_POST['data_label'];
			$sort = $_POST['data_sort'];
			$data = [
				'text' => $label,
				'sort' => $sort,
			];

			$wpdb->update(
				$wpdb->learnpress_question_answers,
				array(
					'answer_data' => serialize($data),
				),
				array( 'question_answer_id' => $answer_id )
			);

		}

		public function sortable_js() {
			wp_enqueue_script(
				'htc-sortable',
				$this->get_plugin_url( 'assets/js/sortable.js' ),
				array( 'jquery', 'jquery-ui-sortable' ),
				LP_ADDON_SORTABLE_VER,
				true
			);
		}
	}
}

add_action( 'plugins_loaded', array( 'LP_Addon_Sortable', 'instance' ) );