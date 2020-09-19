<?php
/**
 * The Template for displaying Post Teaser - C.
 *
 * This template can be overridden by copying it to yourtheme/anwp-post-grid/teaser/teaser--c.php
 *
 * @var object $data - Object with widget data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP_Post_Grid/Templates
 * @since            0.1.0
 *
 * @version          0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'grid_post'           => (object) [],
		'card_height'         => '180',
		'grid_cols'           => '3',
		'grid_cols_tablet'    => '2',
		'grid_cols_mobile'    => '1',
		'show_category'       => 'yes',
		'show_date'           => 'yes',
		'show_comments'       => 'yes',
		'grid_thumbnail_size' => 'medium',
		'wrapper_classes'     => '',
	]
);

$wp_post = $data->grid_post;

/** @var WP_Post $wp_post */
if ( empty( $wp_post->ID ) ) {
	return;
}

// Post Format
$post_format = get_post_format( $wp_post );

// Wrapper Classes
$wrapper_classes = $data->wrapper_classes ? $data->wrapper_classes : anwp_post_grid()->elements->get_teaser_grid_classes( $data, 3, 2, 1 );

// Card Height
$card_height       = ( ! empty( $data->card_height['size'] ) && $data->card_height['size'] >= 150 ) ? absint( $data->card_height['size'] ) : 180;
$card_height_class = 'anwp-pg-height-' . $card_height;
?>
<div class="anwp-pg-post-teaser anwp-pg-post-teaser--layout-c <?php echo esc_attr( $wrapper_classes ); ?>">
	<div class="anwp-pg-post-teaser__thumbnail position-relative">

		<?php if ( in_array( $post_format, [ 'video', 'gallery' ], true ) ) : ?>
			<div class="anwp-pg-post-teaser__format-icon d-flex align-items-center justify-content-center
			<?php echo 'yes' !== $data->show_category && 'yes' !== $data->show_comments ? 'anwp-pg-post-teaser__format-icon--top' : ''; ?>">
				<svg class="anwp-pg-icon anwp-pg-icon--s18 anwp-pg-icon--white">
					<use xlink:href="#icon-anwp-pg-<?php echo esc_attr( 'video' === $post_format ? 'play' : 'device-camera' ); ?>"></use>
				</svg>
			</div>
		<?php endif; ?>

		<div class="anwp-pg-post-teaser__thumbnail-img <?php echo esc_attr( $card_height_class ); ?>"
			style="background-image: url(<?php echo esc_url( anwp_post_grid()->elements->get_post_image_uri( $data->grid_thumbnail_size, true, $wp_post->ID ) ); ?>)">
		</div>

		<div class="anwp-pg-post-teaser__muted_bg"></div>
		<div class="anwp-pg-post-teaser__thumbnail-bg anwp-position-cover"></div>

		<div class="anwp-pg-post-teaser__content d-flex flex-column anwp-position-cover">
			<div class="anwp-pg-post-teaser__top-meta d-flex mb-2">

				<?php
				if ( 'yes' === $data->show_comments ) :

					if ( comments_open( $wp_post->ID ) || get_comments_number( $wp_post->ID ) ) :
						?>
						<span class="anwp-pg-post-teaser__meta-comments d-flex align-items-center mr-2">
							<svg class="anwp-pg-icon anwp-pg-icon--s14 anwp-pg-icon--white mr-1">
								<use xlink:href="#icon-anwp-pg-comment-discussion"></use>
							</svg>
							<?php echo intval( get_comments_number( $wp_post->ID ) ); ?>
						</span>
						<?php
					endif;

					if ( AnWP_Post_Grid::is_pvc_active() ) :
						?>
						<span class="anwp-pg-post-teaser__meta-views d-flex align-items-center mr-2">
							<svg class="anwp-pg-icon anwp-pg-icon--s14 anwp-pg-icon--white mr-1">
								<use xlink:href="#icon-anwp-pg-eye"></use>
							</svg>
							<?php echo intval( pvc_get_post_views( $wp_post->ID ) ); ?>
						</span>
						<?php
					endif;
				endif;

				/*
				|--------------------------------------------------------------------
				| Post Category
				|--------------------------------------------------------------------
				*/
				$post_categories = get_the_category( $wp_post->ID );

				if ( 'yes' === $data->show_category && is_array( $post_categories ) && isset( $post_categories[0]->term_id ) ) :
					anwp_post_grid()->elements->render_post_category_link_filled( $post_categories[0], 'anwp-pg-post-teaser__category-wrapper ml-auto' );
				endif;
				?>

			</div>

			<div class="anwp-pg-post-teaser__bottom-block d-flex align-items-center position-relative py-4 px-1 mb-3 mt-auto">
				<?php if ( 'yes' === $data->show_date ) : ?>
					<div class="anwp-pg-post-teaser__bottom-meta position-absolute">
						<span class="posted-on"><?php echo anwp_post_grid()->elements->get_post_date( $wp_post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</div>
				<?php endif; ?>

				<div class="anwp-pg-post-teaser__title anwp-font-heading">
					<?php echo esc_html( get_the_title( $wp_post->ID ) ); ?>
				</div>
			</div>
		</div>

		<a class="anwp-position-cover anwp-link-without-effects" href="<?php the_permalink( $wp_post ); ?>" aria-hidden="true"></a>
	</div>
</div>
