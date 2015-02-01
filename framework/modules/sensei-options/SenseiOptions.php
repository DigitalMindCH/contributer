<?php

class SenseiOptions {

	private $options_column_name;

	private $raw_options = array();

	private $options = array();

	private $option_properties = array();

	private $option_conditions = array();

	public function __construct( $args, $options_column_name = '' ) {
		//during first initialization (will happen within SenseiAdminPanel), we are going to organize options
		$this->options_column_name = $options_column_name;

		$this->raw_options = $args;

		foreach ( $this->raw_options as $tab ) {
			foreach ( $tab['options'] as $option ) {
				if ( isset( $option['type'] ) && isset( $option['id'] ) ) {
					$class = $this->get_class_name_by_option_type( $option['type'] );

					if ( ! class_exists( $class ) ) {
						continue;
					}

					$this->option_properties[ $option['id'] ]['tab_id'] = $tab['id'];
					$this->option_properties[ $option['id'] ]['option_type'] = $option['type'];
					$this->options[ $option['id'] ] = $class::get_default_option( $option );

					if ( isset( $option['condition'] ) && is_array( $option['condition'] ) ) {
						$this->option_conditions[ $option['id'] ] = $option['condition'];
					}
				}
			}
		}

		//get option then populate value
		$db_options = maybe_unserialize( get_option( $this->options_column_name ) );

		if ( ! empty( $db_options ) ) {
			$this->options = array_merge( $this->options, $db_options );
		}

		foreach ( $this->option_conditions as $option_id => $option_condition ) {
			if ( 'option' == $option_condition['type'] ) {
				$this->option_conditions[ $option_id ]['old_condition_option_value'] = $this->get_option( $option_condition['value'] );
			}
		}

		add_action( 'wp_ajax_save_options', array( $this, 'ajax_save_options' ) );
		add_action( 'wp_ajax_reset_options_tab', array( $this, 'ajax_reset_options_tab' ) );
		add_action( 'wp_ajax_reset_options_all', array( $this, 'ajax_reset_options_all' ) );
	}


	public static function get_instance( $args = array(), $options_column_name = '' ){
		global $sensei_options;

		if ( null == $sensei_options ) {
			$sensei_options = new SenseiOptions( $args, $options_column_name );
		}

		return $sensei_options;
	}


	public function get_condition( $option_id ) {
		if ( isset( $this->option_conditions[ $option_id ] ) && is_array( $this->option_conditions[ $option_id ] ) ) {
			return $this->option_conditions[ $option_id ];
		}
		else {
			return false;
		}
	}


	public function get_option_type( $option_id ) {
		if ( isset ( $this->option_properties[ $option_id ] ) ) {
			return $this->option_properties[ $option_id ]['option_type'];
		}
		else {
			return null;
		}
	}


	public function get_option_tab_id( $option_id ) {
		if ( isset ( $this->option_properties[ $option_id ] ) ) {
			return $this->option_properties[ $option_id ]['tab_id'];
		}
		else {
			return null;
		}
	}


	public function get_option( $option_id ) {

		$option_type = $this->get_option_type( $option_id );
		$option_value = '';

		if ( 'checkbox' == $option_type ) {
			$option_value = (bool) $this->options[ $option_id ];
		}
		else {
			$option_value = $this->options[ $option_id ];
		}

		return $option_value;
	}


	public function ajax_save_options() {

		$status = false;
		$denied = false;

		$tab_id = '';
		if ( isset( $_POST['tab'] ) && ! empty( $_POST['tab'] ) ) {
			$tab_id = filter_input( INPUT_POST, 'tab' );
		}

		$nonce = '';
		if ( isset( $_POST['sensei_options_nonce'] ) && ! empty( $_POST['sensei_options_nonce'] ) ) {
			$nonce = filter_input( INPUT_POST, 'sensei_options_nonce' );
		}

		//these conditions are required in order to proceed with save
		if (
			! empty( $tab_id ) &&
			wp_verify_nonce( $nonce, 'sensei-save-options-'.$tab_id )
		) {
			// @codingStandardsIgnoreStart
			unset( $_POST['tab'] );
			unset( $_POST['action'] );
			unset( $_POST['sensei_options_nonce'] );
			// @codingStandardsIgnoreEnd

			//updating options
			foreach ( $this->options as $option_id => $option_value ) {

				if ( $tab_id != $this->get_option_tab_id( $option_id ) ) {
					continue;
				}

				//check is updated allowed, if not, use old value and skip iteration
				if ( ! $this->is_option_condition_ok( $option_id ) ) {
					$this->options[ $option_id ] = $option_value;
					continue;
				}

				//if update is allowed, proceed
				$option_type = $this->get_option_type( $option_id );

				if ( ! empty ( $option_type ) ) {
					$class = $this->get_class_name_by_option_type( $option_type );

					if ( class_exists( $class ) ) {
						$option_value = $class::get_value_from_post( $option_id );
						$this->options[ $option_id ] = $option_value;
					}
				}
			}

			update_option( $this->options_column_name, $this->options );
			$status = true;
		}
		else {
			$denied = true;
		}

		//check did any of condition values updated (so we can update frontend)
		//TODO: This should be implemented within separate function
		$updated_conditions = array();
		foreach ( $this->option_conditions as $option_id => $option_condition ) {
			if ( 'option' == $option_condition['type'] ) {
				if (
					( $option_condition['old_condition_option_value'] != $this->get_option( $option_condition['value'] ) || (bool) $option_condition['old_condition_option_value'] != (bool) $this->get_option( $option_condition['value'] ) ) &&
					! in_array( $option_condition['value'], $updated_conditions )
				) {
					$updated_conditions[] = $option_condition['value'];
				}
			}
		}

		$return = array(
			'status' => $status,
			'updated_conditions' => $updated_conditions,
		);

		wp_send_json( $return );
	}


	public function ajax_reset_options_tab() {
		$status = true;

		$tab_id = '';
		if ( isset( $_POST['tab_id'] ) && ! empty( $_POST['tab_id'] ) ) {
			$tab_id = filter_input( INPUT_POST, 'tab_id' );
		}
		else {
			$status = false;
		}

		if ( $status ) {
			foreach ( $this->raw_options as $tab ) {

				if ( $tab_id != $tab['id'] ) {
					continue;
				}

				foreach ( $tab['options'] as $option ) {
					if ( isset( $option['type'] ) && isset( $option['id'] ) ) {
						$class = $this->get_class_name_by_option_type( $option['type'] );

						if ( ! class_exists( $class ) ) {
							continue;
						}

						$this->options[ $option['id'] ] = $class::get_default_option( $option );
					}
				}
			}

			update_option( $this->options_column_name, $this->options );
		}

		$return = array(
			'status' => $status,
		);

		wp_send_json( $return );
	}


	public function ajax_reset_options_all() {
		$status = true;

		foreach ( $this->raw_options as $tab ) {

			foreach ( $tab['options'] as $option ) {
				if ( isset( $option['type'] ) && isset( $option['id'] ) ) {
					$class = $this->get_class_name_by_option_type( $option['type'] );

					if ( ! class_exists( $class ) ) {
						continue;
					}

					$this->options[ $option['id'] ] = $class::get_default_option( $option );
				}
			}
		}

		update_option( $this->options_column_name, $this->options );

		$return = array(
			'status' => $status,
		);

		wp_send_json( $return );
	}


	public function is_option_condition_ok( $option_id ) {

		$condition_status = true;
		$condition = $this->get_condition( $option_id );

		if ( false !== $condition ) {
			if ( 'option' == $condition['type'] ) {
				$class_name = $this->get_class_name_by_option_type( $this->get_option_type( $condition['value'] ) );

				if ( class_exists( $class_name ) ) {
					$condition_status = $class_name::is_option_condition( $condition['value'] );
				}
			}
			else if ( 'custom' == $condition['type'] ) {
				$function = $condition['value'];

				//it can be an method as wel
				if ( is_array( $function ) ) {
					$object = $function[0];
					$method_name = $function[1];

					if ( is_object( $object ) && is_callable( $function ) ) {
						$condition_status = $object->$method_name();
					}
					else if ( is_string( $object ) && is_callable( $function ) ) {
						$condition_status = $object::$method_name();
					}
				}
				else {
					if ( is_callable( $function ) ) {
						$condition_status = call_user_func( $function );
					}
				}
			}
		}

		return $condition_status;
	}


	public function get_class_name_by_option_type( $option_type ) {
		$class_name = 'SenseiOption';
		$pieces = explode( '_', $option_type );

		foreach ( $pieces as $piece ) {
			$class_name .= ucfirst( $piece );
		}

		return $class_name;
	}
}

