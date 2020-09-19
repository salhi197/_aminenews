<?php if($totalPage > 1 ): ?>

    <a href="javascript:void(0)" data-tab="<?php echo esc_attr($tabName) ?>" data-total="<?php echo esc_attr($totalPage) ?>" data-paged="<?php echo esc_attr($paged) ?>" class="load_more_btn bdt-button bdt-button-primary bdt-width-medium"> Loading More Items... </a>

<?php endif; ?>