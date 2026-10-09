<?php
/**
 * Section template. Override by copying to yourtheme/client-logos/section.php
 *
 * @var array  $data      From CL_Fields::get() — title, subtitle, description,
 *                        logos[], bg_image, additional_field1..3
 * @var string $uid       Unique id for this instance.
 * @var string $css_class Extra class from the shortcode.
 */

defined( 'ABSPATH' ) || exit;

$title_parts = array_map( 'trim', explode( '|', $data['title'], 2 ) );
$bg_url      = $data['bg_image'] ? wp_get_attachment_image_url( $data['bg_image'], 'large' ) : '';

$icons = array(
	'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6z"/><path d="M8.8 12.2l2.2 2.2 4.3-4.6"/></svg>',
	'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7.5" r="2.6"/><circle cx="5.6" cy="9.5" r="2"/><circle cx="18.4" cy="9.5" r="2"/><path d="M7.5 19v-1.5a4.5 4.5 0 019 0V19z"/><path d="M2.5 18.5v-1a3 3 0 014.3-2.7M21.5 18.5v-1a3 3 0 00-4.3-2.7"/></svg>',
	'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9l3-2 3 1 3-2 3 1 3-1 3 2 2 1"/><path d="M2 9v5l3 1M22 10v4l-3 1"/><path d="M8 13l3 3c.6.6 1.4.6 2 0l.4-.4M10 12l3.5 3.5c.6.6 1.4.6 2 0s.6-1.4 0-2L12 10l-2 1.3c-.7.4-1.5.2-1.9-.4-.4-.7-.2-1.5.4-1.9L11 7"/></svg>',
);

$features = array();
for ( $n = 1; $n <= 3; $n++ ) {
	$text = $data[ 'additional_field' . $n ];
	if ( '' !== $text ) {
		$features[] = array( 'text' => $text, 'icon' => $icons[ $n - 1 ] );
	}
}
?>
<section class="cl-section<?php echo $data['slider']['arrows'] ? '' : ' cl-no-arrows'; ?><?php echo $data['slider']['dots'] ? '' : ' cl-no-dots'; ?> <?php echo esc_attr( $css_class ); ?>" id="<?php echo esc_attr( $uid ); ?>" data-slider="<?php echo esc_attr( wp_json_encode( $data['slider'] ) ); ?>"<?php echo $data['title'] ? ' aria-labelledby="' . esc_attr( $uid ) . '-title"' : ''; ?>>

	<svg class="cl-corner" viewBox="0 0 170 170" aria-hidden="true" focusable="false">
		<polygon points="0,0 72,0 0,98" fill="#13306f"/>
		<polygon points="112,0 168,0 0,162 0,110" fill="#4fa82a"/>
	</svg>

	<?php if ( $bg_url ) : ?>
		<div class="cl-bgimg" style="background-image:url('<?php echo esc_url( $bg_url ); ?>')" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="cl-inner">
		<?php if ( $data['subtitle'] ) : ?>
			<p class="cl-eyebrow"><?php echo esc_html( $data['subtitle'] ); ?></p>
		<?php endif; ?>

		<?php if ( $data['title'] ) : ?>
			<h2 class="cl-title" id="<?php echo esc_attr( $uid ); ?>-title">
				<span class="cl-title__a"><?php echo esc_html( $title_parts[0] ); ?></span>
				<?php if ( ! empty( $title_parts[1] ) ) : ?>
					<span class="cl-title__b"><?php echo esc_html( $title_parts[1] ); ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ( $data['description'] ) : ?>
			<p class="cl-lede"><?php echo nl2br( esc_html( $data['description'] ) ); ?></p>
		<?php endif; ?>

		<?php if ( $data['logos'] ) : ?>
			<div class="cl-carousel" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Memberships', 'client-logos' ); ?>">
				<?php if ( $data['slider']['arrows'] ) : ?>
				<button class="cl-arrow cl-arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Previous logo', 'client-logos' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
				</button>
				<?php endif; ?>
				<div class="cl-card">
					<ul class="cl-track">
						<?php foreach ( $data['logos'] as $logo ) :
							$alt = $logo['alt'] ?: get_post_meta( $logo['id'], '_wp_attachment_image_alt', true );
							$img = wp_get_attachment_image( $logo['id'], 'medium', false, array( 'alt' => $alt, 'loading' => 'lazy', 'class' => 'cl-logo' ) );
							if ( ! $img ) { continue; }
							?>
							<li class="cl-slot">
								<?php if ( $logo['link'] ) : ?>
									<a href="<?php echo esc_url( $logo['link'] ); ?>" target="_blank" rel="noopener"><?php echo $img; // phpcs:ignore ?></a>
								<?php else : ?>
									<?php echo $img; // phpcs:ignore ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php if ( $data['slider']['arrows'] ) : ?>
				<button class="cl-arrow cl-arrow--next" type="button" aria-label="<?php esc_attr_e( 'Next logo', 'client-logos' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
				</button>
				<?php endif; ?>
			</div>
			<?php if ( $data['slider']['dots'] ) : ?>
				<div class="cl-dots" aria-label="<?php esc_attr_e( 'Choose logo', 'client-logos' ); ?>"></div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<div class="cl-band">
		<svg class="cl-wave" viewBox="0 0 1600 150" preserveAspectRatio="none" aria-hidden="true" focusable="false">
			<path d="M0 8 Q800 72 1600 8 L1600 150 L0 150 Z" fill="#0e2a63"/>
			<path d="M0 22 C70 60 130 105 178 150 L0 150 Z" fill="#4fa82a"/>
			<path d="M0 62 C60 90 110 120 140 150 L0 150 Z" fill="#1d58c9"/>
			<path d="M1600 22 C1530 60 1470 105 1422 150 L1600 150 Z" fill="#4fa82a"/>
			<path d="M1600 62 C1540 90 1490 120 1460 150 L1600 150 Z" fill="#1d58c9"/>
		</svg>
		<?php if ( $features ) : ?>
			<div class="cl-features">
				<ul>
					<?php foreach ( $features as $f ) : ?>
						<li class="cl-feature">
							<span class="cl-feature__icon" aria-hidden="true"><?php echo $f['icon']; // phpcs:ignore -- static SVG. ?></span>
							<span class="cl-feature__text"><?php echo esc_html( $f['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
