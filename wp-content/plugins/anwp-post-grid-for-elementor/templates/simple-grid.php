<?php
/**
 * The Template for displaying Simple Grid.
 *
 * This template can be overridden by copying it to yourtheme/anwp-post-grid/simple-grid.php
 *
 * @var object $data - Object with widget data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP_Post_Grid/Templates
 * @since            0.1.0
 *
 * @version          0.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'grid_posts'        => [],
		'layout'            => 'a',
		'grid_gutter'       => 'thick',
		'offset'            => 0,
		'show_load_more'    => false,
		'load_more_label'   => '',
		'load_more_class'   => '',
		'posts_per_load'    => 3,
		'grid_widget_title' => '',
		'header_size'       => 'h3',
		'header_icon'       => '',
	]
);

if ( empty( $data->grid_posts ) ) {
	return;
}
?>
<div class="anwp-pg-wrap">

	<?php if ( ! empty( $data->grid_widget_title ) ) : ?>
		<div class="anwp-pg-widget-header d-flex align-items-center position-relative">
			<?php
			// Icon

			// Render title
			printf(
				'<%1$s class="d-flex align-items-center flex-wrap anwp-pg-widget-header__title">%3$s %2$s</%1$s>',
				esc_attr( $data->header_size ),
				esc_html( $data->grid_widget_title ),
				$data->header_icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
			<div class="anwp-pg-widget-header__secondary-line"></div>
		</div>
	<?php endif; ?>

	<div class="d-flex row flex-wrap anwp-pg-simple-grid anwp-pg-posts-wrapper anwp-pg-simple-grid__gutter-<?php echo esc_attr( $data->grid_gutter ); ?> <?php echo esc_attr( 'thick' !== $data->grid_gutter ? 'no-gutters' : '' ); ?>">
		<?php
		foreach ( $data->grid_posts as $grid_post ) {
			$data->grid_post = $grid_post;
			anwp_post_grid()->load_partial( $data, 'teaser/teaser', sanitize_key( $data->layout ) );
		}
		?>
	</div>
	<?php if ( $data->show_load_more ) : ?>
		<div class="w-100 anwp-pg-load-more">
			<button class="button btn btn-outline-secondary anwp-pg-load-more__btn mx-auto d-block my-3 <?php echo esc_attr( $data->load_more_class ); ?>" type="button"
				data-anwp-loaded-qty="<?php echo absint( count( $data->grid_posts ) + $data->offset ); ?>"
				data-anwp-posts-per-load="<?php echo absint( $data->posts_per_load ); ?>"
				data-anwp-load-more="<?php echo esc_attr( anwp_post_grid()->elements->get_serialized_load_more_data( $data, 'classic-grid' ) ); ?>">
				<span class="anwp-pg-load-more__label"><?php echo empty( $data->load_more_label ) ? esc_html__( 'load more', 'anwp-post-grid' ) : esc_html( $data->load_more_label ); ?></span>
				<span class="anwp-pg-wave d-flex align-items-center">
					<span class="anwp-pg-rect anwp-pg-rect1"></span>
					<span class="anwp-pg-rect anwp-pg-rect2"></span>
					<span class="anwp-pg-rect anwp-pg-rect3"></span>
					<span class="anwp-pg-rect anwp-pg-rect4"></span>
					<span class="anwp-pg-rect anwp-pg-rect5"></span>
				</span>
			</button>
		</div>
	<?php endif; ?>
</div>
