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
							$return['mark'] += 1;

						} else {
							$correct[] = false;
						}

						$this->_set_checked( $return, $user_answer );//
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
		 * Do something before get data.
		 * Some data now is not auto loading when object is created
		 * therefore, we will load it here.
		 *
		 * @param string $name
		 * @param string $default
		 *
		 * @return array|mixed
		 */
		public function get_data( $name = '', $default = '' ) {
			switch ( $name ) {
				case 'answer_options':
					$answer_options = parent::get_data( $name, $default );

					if ( ! $answer_options ) {
						$answer_options = $this->_curd->load_answer_options( $this->get_id() );
						$this->set_data( $name, $answer_options );
					}

					break;
				case 'mark':
					$answers = $this->get_answers();
					$count = 0;
					foreach ( $answers as $key => $option ) {
						$count++;
					}
					$this->set_data( $name, $count );
					break;
			}

			return parent::get_data( $name, $default );
		}

	}
}
