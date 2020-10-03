<?php
$actions = new Secure_Copy_Content_Protection_Subscribe_Actions($this->plugin_name);

if (isset($_REQUEST['ays_submit'])) {
    $actions->store_data($_REQUEST);
}

$data = $actions->get_data();
$data_lastIds = $actions->sccp_get_bs_last_id();
$data_lastId = (array) $data_lastIds;

$bs_last_id = $data_lastId['AUTO_INCREMENT'];

?>
<div class="wrap" style="position:relative;">
    <div class="container-fluid">
        <form method="post">
            <h1 class="wp-heading-inline">
                <?php
                echo __('Subscribe to view', $this->plugin_name);
                ?>
            </h1>
            <?php
            if (isset($_REQUEST['status'])) {
                $actions->sccp_subscribe_notices($_REQUEST['status']);
            }
            ?>
            <hr/>
            <div class="ays-settings-wrapper">
                                    
                <button type="button" class="button add_new_block_subscribe"
                        style="margin-bottom: 20px"><?= __('Add new', $this->plugin_name); ?></button>
                <div class="all_block_subscribes" data-last-id="<?php echo $bs_last_id; ?>">
                    <?php
                     foreach ( $data as $key => $blocsubscribe ) { 
                        $block_id = isset($blocsubscribe['id']) ? absint( intval($blocsubscribe['id'])) : $bs_last_id;
                        $block_options = isset($blocsubscribe['options']) ? json_decode($blocsubscribe['options'], true) : array();
                        $block_sub_require_verification = isset($block_options['require_verification']) && $block_options['require_verification'] == 'on' ? 'checked' : '';
                    ?>
                        <div class="blockcont_one" id="blocksub<?php echo $block_id; ?>">
                            <div class="copy_protection_container form-group row ays_bc_row">
                                <div class="col sccp_block_sub">
                                    <div class="sccp_block_sub_label_inp">
                                        <div class="sccp_block_sub_label">
                                            <label for="sccp_block_subscribe_shortcode_<?php echo $block_id; ?>" class="sccp_bc_label"><?= __('Shortcode', $this->plugin_name); ?></label>
                                        </div>                                    
                                        <div class="sccp_block_sub_inp">
                                            <input type="text" name="sccp_block_subscribe_shortcode[]" id="sccp_block_subscribe_shortcode_<?php echo $block_id; ?>"
                                                   class="ays-text-input sccp_blockcont_shortcode select2_style"
                                                   value="[ays_block_subscribe id='<?php echo $block_id; ?>'] Content [/ays_block_subscribe]"
                                                   readonly>
                                            <input type="hidden" name="sccp_blocksub_id[]" value="<?php echo $block_id; ?>">
                                        </div>                                        
                                    </div>
                                    <div class="sccp_block_sub_inp_row">
                                        <div class="sccp_pro " title="<?= __('This feature will available in PRO version', $this->plugin_name); ?>">
                                            <div class="pro_features sccp_general_pro">
                                                <div>
                                                    <p style="font-size: 16px !important;">
                                                        <?= __("This feature is available only in ", $this->plugin_name); ?>
                                                        <a href="https://ays-pro.com/index.php/wordpress/secure-copy-content-protection"
                                                           target="_blank"
                                                           title="PRO feature"><?= __("PRO version!!!", $this->plugin_name); ?></a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="sccp_block_sub_label">
                                                <label for="sccp_require_verification_<?php echo $block_id; ?>" class="sccp_bc_label"><?= __('Require verification', $this->plugin_name); ?></label>
                                            </div>
                                            <div class="sccp_block_sub_inp">
                                                <input type="checkbox" name="sccp_subscribe_require_verification[]" id="sccp_require_verification_<?php echo $block_id; ?>"
                                                       class="ays-text-input sccp_blocksub select2_style" value="on"
                                                       <?php echo  $block_sub_require_verification; ?>
                                                       >
                                                <input type="hidden" name="sub_require_verification[]" class="sccp_blocksub_hid" value="<?php echo isset($block_options['require_verification']) && $block_options['require_verification'] == 'on' ? 'on' : 'off'; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <br>
                                    <p class="blocksub_delete_icon"><i class="ays_fa fa-trash-o" aria-hidden="true"></i>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <input type="hidden" class="deleted_ids" value="" name="deleted_ids">
                </div>
                <button type="button" class="button add_new_block_subscribe"
                        style="margin-top: 20px"><?= __('Add new', $this->plugin_name); ?></button> 
                <hr/>                        
            </div>
            <?php
            wp_nonce_field('subscribe_action', 'subscribe_action');
            $other_attributes = array();
            submit_button(__('Save changes', $this->plugin_name), 'primary ays-button', 'ays_submit', true, $other_attributes);
            ?>
        </form>
    </div>
</div>