<?php
/**
 * Question sortable question class.
 *
 * @author   ThimPress
 * @package  LearnPress/Fill-In-Blank/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Question_Sortable' ) ) {

	/**
	 * Class LP_Question_Sortable
	 */
	class LP_Question_Sortable extends LP_Question {

		/**
		 * @var string
		 */
		protected $_question_type = 'sortable';
		public static $__sort = array();

		/**
		 * Do not support answer options
		 *
		 * @var bool
		 */
		protected $_answer_options = false;

		/**
		 * @var array
		 */
		protected $_answer = false;

		/**
		 * @var bool
		 */
		public $_sorts = array();

		/**
		 * LP_Question_Sortable constructor.
		 *
		 * @param null $the_question
		 * @param null $args
		 *
		 * @throws Exception
		 */
		public function __construct( $the_question = null, $args = null ) {

			parent::__construct( $the_question, $args );

			# make sure that shortcode is not exists before add it

			if(shortcode_exists('sortable')){
				remove_shortcode('sortable');
			}
			if ( ! shortcode_exists( 'sortable' ) ) {
				add_shortcode( 'sortable', array( $this, 'add_shortcode' ) );
			}

			if ( $answers = $this->get_answers() ) {
				foreach ( $answers as $k => $answer ) {
					$list_sort[] = $answer->get_value();
					$this->_sorts[ $k ] = $answer->get_value();

				}
			}

			if( !isset(self::$__sort[$this->get_id()])){
				self::$__sort[$this->get_id()] = $this->_sorts;
			}
			add_filter( 'learn-press/question-editor/localize-script', array(
				$this,
				'sanitize_question_answers'
			), 1000 );

		}

		function sanitize_question_answers( $data ) {
			global $post;
			if ( $post && ( $post->ID == $this->get_id() ) && isset( $data['root'] ) ) {
				if ( isset( $data['root']['answers'] ) ) {
					$answers     = array();
					$old_answers = reset( $data['root']['answers'] );
					foreach ( $old_answers as $k => $v ) {
						if ( $k == "0" ) {
							$answers['text'] = str_replace(array('=\"', '\"'), array('="', '"'),  $v );
						} else {
							$answers[ $k ] = $v;
						}
					}
					$data['root']['answers'] = array( $answers );
				}
			}

			return $data;
		}

		/**
		 * Get passage.
		 *
		 * @param bool $checked
		 *
		 * @return mixed|null|string|string[]
		 */
		public function get_passage( $checked = false ) {
			$passage = $this->get_data( 'answer_options' );
			if ( $checked ) {
				$pattern = $this->get_wp_shortcode_regex();
				$passage = preg_replace_callback( $pattern, array( $this, '_replace_callback' ), $passage );
			}
			global $wp_filter;

			if ( ! empty( $wp_filter['wpautop'] ) ) {
				$wpautop = $wp_filter['wpautop'];
				unset( $wp_filter['wpautop'] );
			}
			$passage = do_shortcode( $passage );

			if ( isset( $wpautop ) ) {
				$wp_filter['wpautop'] = $wpautop;
			}

			return preg_replace( '!^<p>|<\/p>$!', '', $passage );
		}

		/**
		 * Replace callback.
		 *
		 * @param $a
		 *
		 * @return string
		 */
		public function _replace_callback( $a ) {
			$user_fill = '';

			$attr = shortcode_parse_atts( $a[3] );

			if ( ! empty( $this->user_answered ) && array_key_exists( 'fill', $attr ) ) {
				settype( $this->user_answered, 'array' );
				$input_name = $this->get_input_name( $attr['fill'] );
				if ( ! empty( $this->user_answered[ $input_name ] ) ) {
					settype( $this->user_answered[ $input_name ], 'array' );
					$user_fill = array_shift( $this->user_answered[ $input_name ] );
				}
			}

			$atts = shortcode_parse_atts( $a[3] );

			return "[sortable " . $a[3] . ' correct_fill="' . $atts['fill'] . '" user_fill="' . $user_fill . '"]';
		}

		/**
		 * Get Fill in blank default answers.
		 *
		 * @return array|bool|string
		 */
		public function get_default_answers() {
			$default = array(
				array(
					'is_true' => 'yes',
					'value'   => '',
					'text'    => '',
				)
			);

			return $default;
		}

		/**
		 * Prints the question in frontend user.
		 *
		 * @param bool $args
		 */
		public function render( $args = false ) {
			learn_press_sortable_get_template( 'answer.php', array( 'question' => $this ) );
		}

		protected function get_blank_data() {

		}

		/**
		 * Add fill in blank question shortcode.
		 *
		 * @param null $atts
		 *
		 * @return string
		 */
		public function add_shortcode( $atts = null ) {
			$quiz 	= LP_Global::course_item_quiz();
			$current_question_id = $quiz->get_viewing_question( 'id' );
			$question = learn_press_get_question($current_question_id);
			$answered = $question->get_answered();
			if ( false === ( $checked = $question->_get_checked( $answered ) ) ) {
				$checked = $question->check( $answered );
			}
			
			$atts = shortcode_atts(
				array(
					'fill'      => '',
					'uid'       => '',
					'id'        => '',
					'user_fill' => '',
					'correct'   => ''
				), $atts
			);

			$uid = $atts['id'];
			if ( ! empty( $answered[ $uid ] ) ) {
				$atts['user_fill'] = $answered[ $uid ];
				if ( ! empty( $checked['sort'][ $uid ] ) ) {
					$atts['correct'] = $checked['sort'][ $uid ];
				} else {

				}
			}
			ob_start();

			global $wp_filter;

			if ( ! empty( $wp_filter['wpautop'] ) ) {
				$wpautop = $wp_filter['wpautop'];
				unset( $wp_filter['wpautop'] );
			}

			learn_press_sortable_get_template(
				'blank.php',
				array(
					'question' => $question,
					'answer'   => $question->_answer,
					'blank'    => array_merge( $question->_sorts[ $atts['id'] ], $atts )
				)
			);

			if ( isset( $wpautop ) ) {
				$wp_filter['wpautop'] = $wpautop;
			}

			return ob_get_clean();
		}

		/**
		 * Get input name.
		 *
		 * @param $fill
		 *
		 * @return string
		 */
		public function get_input_name( $fill ) {
			return '_' . md5( wp_create_nonce( $fill ) );
		}

		/**
		 * Set text format.
		 *
		 * @param $text
		 *
		 * @return string
		 */
		private function _format_text( $text ) {
			return trim( preg_replace( '!\s+!', ' ', $text ) );
		}

		/**
		 * Check user answer.
		 *
		 * @param null $user_answer
		 *
		 * @return mixed
		 */
		public function check( $user_answer = null ) {

			if ( $return = $this->_get_checked( $user_answer ) ) {
				return $return;
			}

			$return = parent::check();

			if ( $this->_sorts && ( $answered = $user_answer ) ) {
				$return['sorts'] = array();
				$point_per_blank  = $this->get_mark() / sizeof( $this->_sorts );
				$correct = array();
				$list_sort = $this->_sorts;
				$sorts = array();
				foreach ( $list_sort as $key => $sort ) {
					$sorts[] = $sort;
				}

				if ( $answers = $this->get_answers() ) {
					foreach ( $answers as $key => $option ) {
						$sort = $sorts[ (int) $option['answer_order'] - 1 ];
						if ( ( $option['sort'] == $answered[$option['answer_order']] ) ) {
							$correct[] = true;
							$return['mark'] += $point_per_blank;
						} else {
							$correct[] = false;
						}
					}
					if ( in_array( false, $correct) ) {
						$return['correct'] = false;
					} else {
						$return['correct'] = true;
					}

					$return['sorts'] = $correct;
				}

				$answered_value = array_values( $answered );
				$value          = array_filter( $answered_value );

				if ( empty( $value ) ) {
					$return['answered'] = false;
				}
			}

			$this->_set_checked( $return, $user_answer );

			return $return;
		}

		/**
		 * Get Wordpress shortcode regex.
		 *
		 * @return string
		 */
		public function get_wp_shortcode_regex() {
			return '/' . get_shortcode_regex( array( 'sortable' ) ) . '/';
		}

	}
}
