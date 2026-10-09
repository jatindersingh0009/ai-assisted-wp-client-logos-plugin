<?php
/**
 * Edit screen: fields meta box, shortcode meta box, admin assets,
 * and a fallback save when the post itself is saved/published.
 */

defined( 'ABSPATH' ) || exit;

class CL_Metabox {

	const NONCE = 'cl_save_meta';

	public static function init() {
		add_action( 'add_meta_boxes_' . CL_POST_TYPE, array( __CLASS__, 'add' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'save_post_' . CL_POST_TYPE, array( __CLASS__, 'save_with_post' ), 10, 2 );
	}

	public static function add() {
		add_meta_box( 'cl-metabox', __( 'Section content', 'client-logos' ), array( __CLASS__, 'render' ), CL_POST_TYPE, 'normal', 'high' );
		add_meta_box( 'cl-shortcode', __( 'Shortcode', 'client-logos' ), array( __CLASS__, 'render_shortcode' ), CL_POST_TYPE, 'side', 'high' );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || CL_POST_TYPE !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cl-admin', CL_URL . 'assets/css/admin.css', array(), CL_VERSION );
		wp_enqueue_script( 'cl-admin', CL_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), CL_VERSION, true );
		wp_localize_script( 'cl-admin', 'clAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( self::NONCE ),
			'postId'  => get_the_ID(),
			'i18n'    => array(
				'chooseLogo'   => __( 'Choose logo', 'client-logos' ),
				'chooseLogos'  => __( 'Choose logos', 'client-logos' ),
				'chooseBg'     => __( 'Choose background image', 'client-logos' ),
				'use'          => __( 'Use this image', 'client-logos' ),
				'useMany'      => __( 'Add selected logos', 'client-logos' ),
				'confirmDel'   => __( 'Delete this logo?', 'client-logos' ),
				'saving'       => __( 'Saving…', 'client-logos' ),
				'saved'        => __( 'Saved', 'client-logos' ),
				'unsaved'      => __( 'Unsaved changes', 'client-logos' ),
				'error'        => __( 'Not saved. Check your connection and try again.', 'client-logos' ),
				'skipped'      => __( '%d row(s) had no image and were not saved.', 'client-logos' ),
			),
		) );
	}

	/** One repeater row. $i is the index or the __i__ template token. */
	public static function logo_row( $i, $row = array() ) {
		$id   = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
		$alt  = isset( $row['alt'] ) ? $row['alt'] : '';
		$link = isset( $row['link'] ) ? $row['link'] : '';
		$name = 'cl[logos][' . $i . ']';
		$img  = $id ? wp_get_attachment_image( $id, 'medium', false, array( 'alt' => '' ) ) : '';
		ob_start();
		?>
		<li class="cl-row<?php echo $id ? '' : ' cl-row--empty'; ?>">
			<span class="cl-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'client-logos' ); ?>"></span>
			<button type="button" class="cl-thumb cl-choose" aria-label="<?php esc_attr_e( 'Choose image', 'client-logos' ); ?>">
				<?php echo $img ? $img : '<span class="cl-thumb__empty">' . esc_html__( 'Choose image', 'client-logos' ) . '</span>'; ?>
			</button>
			<input type="hidden" class="cl-logo-id" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $id ?: '' ); ?>">
			<div class="cl-row__fields">
				<input type="text" class="widefat" name="<?php echo esc_attr( $name ); ?>[alt]" value="<?php echo esc_attr( $alt ); ?>" placeholder="<?php esc_attr_e( 'Organisation name (used as alt text)', 'client-logos' ); ?>">
				<input type="url" class="widefat" name="<?php echo esc_attr( $name ); ?>[link]" value="<?php echo esc_attr( $link ); ?>" placeholder="<?php esc_attr_e( 'Link, optional (https://…)', 'client-logos' ); ?>">
			</div>
			<button type="button" class="button-link cl-remove"><?php esc_html_e( 'Delete', 'client-logos' ); ?></button>
		</li>
		<?php
		return ob_get_clean();
	}

	public static function render( $post ) {
		$d = CL_Fields::get( $post->ID );
		wp_nonce_field( self::NONCE, 'cl_meta_nonce' );
		$bg_img = $d['bg_image'] ? wp_get_attachment_image( $d['bg_image'], 'medium' ) : '';
		?>
		<div class="cl-box">

			<div class="cl-field">
				<label for="cl-subtitle"><?php esc_html_e( 'Sub title', 'client-logos' ); ?></label>
				<input type="text" id="cl-subtitle" class="widefat" name="cl[subtitle]" value="<?php echo esc_attr( $d['subtitle'] ); ?>" placeholder="Stronger together">
				<p class="description"><?php esc_html_e( 'Small spaced-out line above the title.', 'client-logos' ); ?></p>
			</div>

			<div class="cl-field">
				<label for="cl-title"><?php esc_html_e( 'Title', 'client-logos' ); ?></label>
				<input type="text" id="cl-title" class="widefat" name="cl[title]" value="<?php echo esc_attr( $d['title'] ); ?>" placeholder="Proud Members of Leading | Industry Associations">
				<p class="description"><?php esc_html_e( 'Use | to split the title into a blue line and a green line.', 'client-logos' ); ?></p>
			</div>

			<div class="cl-field">
				<label for="cl-description"><?php esc_html_e( 'Small description', 'client-logos' ); ?></label>
				<textarea id="cl-description" class="widefat" rows="3" name="cl[description]" placeholder="We work according to professional standards…"><?php echo esc_textarea( $d['description'] ); ?></textarea>
			</div>

			<div class="cl-field">
				<div class="cl-field__head">
					<label><?php esc_html_e( 'Logos', 'client-logos' ); ?> <span class="cl-count">(<span id="cl-logo-count"><?php echo count( $d['logos'] ); ?></span>)</span></label>
					<button type="button" class="button" id="cl-bulk-add"><?php esc_html_e( 'Add several from Media Library', 'client-logos' ); ?></button>
				</div>
				<ul id="cl-logo-list" class="cl-list">
					<?php
					foreach ( $d['logos'] as $i => $row ) {
						echo self::logo_row( $i, $row ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in logo_row().
					}
					?>
				</ul>
				<p id="cl-logo-empty" class="cl-empty"<?php echo $d['logos'] ? ' hidden' : ''; ?>><?php esc_html_e( 'No logos yet. Add your first one below.', 'client-logos' ); ?></p>
				<button type="button" class="button cl-add" id="cl-add-logo"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add more', 'client-logos' ); ?></button>
				<script type="text/html" id="tmpl-cl-logo-row"><?php echo self::logo_row( '__i__' ); // phpcs:ignore ?></script>
			</div>

			<div class="cl-field">
				<label><?php esc_html_e( 'Background image', 'client-logos' ); ?></label>
				<div class="cl-bg">
					<div class="cl-bg__preview" id="cl-bg-preview"><?php echo $bg_img; // phpcs:ignore ?></div>
					<input type="hidden" id="cl-bg-id" name="cl[bg_image]" value="<?php echo esc_attr( $d['bg_image'] ?: '' ); ?>">
					<div>
						<button type="button" class="button" id="cl-bg-choose"><?php esc_html_e( 'Choose image', 'client-logos' ); ?></button>
						<button type="button" class="button-link cl-remove" id="cl-bg-remove"<?php echo $d['bg_image'] ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'client-logos' ); ?></button>
						<p class="description"><?php esc_html_e( 'Shown faded on the right side of the section, like the truck in the design.', 'client-logos' ); ?></p>
					</div>
				</div>
			</div>

			<div class="cl-grid3">
				<?php for ( $n = 1; $n <= 3; $n++ ) : $k = 'additional_field' . $n; ?>
					<div class="cl-field">
						<label for="cl-<?php echo esc_attr( $k ); ?>"><?php printf( esc_html__( 'Additional field %d', 'client-logos' ), (int) $n ); ?></label>
						<input type="text" id="cl-<?php echo esc_attr( $k ); ?>" class="widefat" name="cl[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $d[ $k ] ); ?>" placeholder="<?php echo esc_attr( array( 1 => 'Professional Standards', 2 => 'Industry Experience', 3 => 'Reliable Service' )[ $n ] ); ?>">
					</div>
				<?php endfor; ?>
			</div>
			<p class="description"><?php esc_html_e( 'The three additional fields appear as the icon features in the blue band. Leave one empty to hide it.', 'client-logos' ); ?></p>

			<?php self::render_slider_settings( $d['slider'] ); ?>

			<div class="cl-savebar">
				<button type="button" class="button button-primary" id="cl-save"><?php esc_html_e( 'Save section', 'client-logos' ); ?></button>
				<span class="spinner" id="cl-spinner"></span>
				<span class="cl-status" id="cl-status" role="status" aria-live="polite"></span>
			</div>
		</div>
		<?php
	}

	/** Slider settings panel. */
	public static function render_slider_settings( $s ) {
		$lim = CL_Fields::slider_limits();
		$num = function ( $key, $label, $unit, $help = '' ) use ( $s, $lim ) {
			$id = 'cl-slider-' . $key;
			?>
			<div class="cl-setting<?php echo in_array( $key, array( 'delay' ), true ) ? ' cl-setting--timed' : ''; ?>">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
				<span class="cl-setting__input">
					<input type="number" id="<?php echo esc_attr( $id ); ?>" class="small-text" name="cl[slider][<?php echo esc_attr( $key ); ?>]"
						min="<?php echo esc_attr( $lim[ $key ][0] ); ?>" max="<?php echo esc_attr( $lim[ $key ][1] ); ?>" step="<?php echo esc_attr( $lim[ $key ][2] ); ?>"
						value="<?php echo esc_attr( $s[ $key ] ); ?>">
					<?php echo esc_html( $unit ); ?>
				</span>
				<?php if ( $help ) : ?><p class="description"><?php echo esc_html( $help ); ?></p><?php endif; ?>
			</div>
			<?php
		};
		$toggle = function ( $key, $label, $help = '' ) use ( $s ) {
			?>
			<div class="cl-setting cl-setting--toggle">
				<input type="hidden" name="cl[slider][<?php echo esc_attr( $key ); ?>]" value="0">
				<label>
					<input type="checkbox" name="cl[slider][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( (int) $s[ $key ], 1 ); ?> <?php echo 'autoplay' === $key ? 'id="cl-slider-autoplay"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</label>
				<?php if ( $help ) : ?><p class="description"><?php echo esc_html( $help ); ?></p><?php endif; ?>
			</div>
			<?php
		};
		?>
		<fieldset class="cl-panel<?php echo $s['autoplay'] ? '' : ' is-autoplay-off'; ?>" id="cl-slider-panel">
			<legend><?php esc_html_e( 'Slider settings', 'client-logos' ); ?></legend>

			<h4><?php esc_html_e( 'Movement', 'client-logos' ); ?></h4>
			<div class="cl-settings">
				<?php
				$toggle( 'autoplay', __( 'Autoplay', 'client-logos' ), __( 'Moves one logo at a time and loops forever.', 'client-logos' ) );
				$toggle( 'pause_hover', __( 'Pause on logo hover', 'client-logos' ) );
				$num( 'delay', __( 'Wait between moves', 'client-logos' ), __( 'seconds', 'client-logos' ) );
				$num( 'speed', __( 'Slide speed', 'client-logos' ), __( 'ms', 'client-logos' ), __( 'Higher is slower and softer. 600–900 feels smooth.', 'client-logos' ) );
				?>
			</div>

			<h4><?php esc_html_e( 'Logos visible at once', 'client-logos' ); ?></h4>
			<div class="cl-settings">
				<?php
				$num( 'per_desktop', __( 'Desktop', 'client-logos' ), __( 'logos', 'client-logos' ) );
				$num( 'per_tablet', __( 'Tablet', 'client-logos' ), __( 'logos', 'client-logos' ), __( 'Slider narrower than 900px', 'client-logos' ) );
				$num( 'per_mobile', __( 'Mobile', 'client-logos' ), __( 'logos', 'client-logos' ), __( 'Slider narrower than 560px', 'client-logos' ) );
				?>
			</div>

			<h4><?php esc_html_e( 'Controls', 'client-logos' ); ?></h4>
			<div class="cl-settings">
				<?php
				$toggle( 'arrows', __( 'Show arrows', 'client-logos' ) );
				$toggle( 'dots', __( 'Show dots', 'client-logos' ) );
				?>
			</div>
		</fieldset>
		<?php
	}

	public static function render_shortcode( $post ) {
		$code = sprintf( '[client_logos id="%d"]', (int) $post->ID );
		?>
		<p><?php esc_html_e( 'Paste this where the section should appear:', 'client-logos' ); ?></p>
		<input type="text" readonly class="widefat code cl-shortcode-input" value="<?php echo esc_attr( $code ); ?>" onfocus="this.select()">
		<p><button type="button" class="button" id="cl-copy" data-code="<?php echo esc_attr( $code ); ?>"><?php esc_html_e( 'Copy shortcode', 'client-logos' ); ?></button></p>
		<?php if ( 'publish' !== $post->post_status ) : ?>
			<p class="description"><?php esc_html_e( 'Publish this section so visitors can see it. Until then only editors see it.', 'client-logos' ); ?></p>
		<?php endif;
	}

	/** Fallback: also save fields when Update/Publish is clicked. */
	public static function save_with_post( $post_id, $post ) {
		if ( ! isset( $_POST['cl_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cl_meta_nonce'] ) ), self::NONCE ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$raw = isset( $_POST['cl'] ) ? wp_unslash( $_POST['cl'] ) : array(); // phpcs:ignore -- sanitised in CL_Fields.
		CL_Fields::save( $post_id, $raw );
	}
}
