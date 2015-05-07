<?php

class SenseiAdminPanel {

	private $url;

	private $menu_page_parameters = array();

	private $tabs_parameters = array();


	public function __construct( $url, $args ) {
		$this->url = $url;
		$this->menu_page_parameters = $args['page'];
		$this->tabs_parameters = $args['tabs'];
		Sensei_Options::get_instance( $args['tabs'], $this->menu_page_parameters['menu_slug'] );

		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
	}


	public function register_menu_page() {
		$sensei_admin_page = add_menu_page(
						$this->menu_page_parameters['page_title'],
						$this->menu_page_parameters['menu_title'],
						$this->menu_page_parameters['capability'],
						$this->menu_page_parameters['menu_slug'],
						array( $this, 'render_page' ),
						$this->menu_page_parameters['icon_url']
		);

		add_action( 'load-' . $sensei_admin_page, array( $this, 'sensei_scripts_loader' ) );
	}


	public function sensei_scripts_loader() {
		add_action( 'admin_enqueue_scripts', array( $this, 'sensei_admin_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'sensei_admin_css' ) );
	}


	public function sensei_admin_js() {
		wp_enqueue_script( 'sensei-options', $this->url.'/js/sensei-options.js', array( 'jquery' ), '1.0' );
	}


	public function sensei_admin_css() {
		wp_enqueue_style( 'sensei-options', $this->url.'/css/sensei-options.css', false, '1.0' );
	}


	public function render_page() {
		?>
		<div class="sensei-panel-container">
			
			<div class="sensei-tab-menu-container">
				<ul>
					<?php $tab_class = 'tab-selected' ?>
					<?php foreach ( $this->tabs_parameters as $tab ) { ?>
						<li id="<?php echo sanitize_html_class( $tab['id'] ); ?>" class="sensei-tab <?php echo sanitize_html_class( $tab_class ); ?>" >
							<span class="tab-icon"></span>
							<span class="tab-title"><?php echo esc_html( $tab['title'] ); ?></span>
						</li>
						<?php $tab_class = ''; ?>
					<?php } ?>
				</ul>
				<div class="clearfix"></div>
			</div>
			
			<div class="sensei-tab-content-container">
				<?php $visibility = 1; ?>
				<?php foreach ( $this->tabs_parameters as $tab ) { ?>				
					<div id="tab-content-<?php echo sanitize_html_class( $tab['id'] ); ?>" class="tab-content" style="<?php if ( $visibility ) { ?> display:block;  <?php } ?>">
						<form id="sensei-options-form-<?php echo sanitize_html_class( $tab['id'] ); ?>" class="sensei-options-form" >
							
							<input type="hidden" name="tab" value="<?php echo $tab['id']; //xss ok ?>" >
							<input type="hidden" name="action" value="save_options" />
							<?php wp_nonce_field( 'sensei-save-options-'.$tab['id'], 'sensei_options_nonce', false ); ?>
							
							<?php 
							foreach ( $tab['options'] as $option ) {
								new SenseiOptionsRenderer( $option );
							}
							?>
							
							<div class="sensei-option-container">
								<div class="spinner"></div>
								<input type="submit" class="sensei-submit" name="save-<?php echo sanitize_key( $tab['id'] ); ?>" value="Save" />
								<div class="sensei-reset-buttons">
									<span class="sensei-reset-tab sensei-submit" data-tab="<?php echo sanitize_html_class( $tab['id'] ); ?>">Reset tab</span>
									<span class="sensei-submit sensei-reset-all">Reset all</span>
								</div>
								<div class="clear"></div>
							</div>
							
						</form>
					</div>
				<?php $visibility = 0; } ?>
			</div>
			
		</div>
		<?php
	}
}

