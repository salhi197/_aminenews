<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
            echo esc_html(get_admin_page_title());
        ?>
    </h1>
    
    <!-- <a href="https://ays-pro.com/wordpress/secure-copy-content-protection/" target="_blank"><button class="disabled-button" style="float: right; margin-right: 5px;" title="<?php // echo __('This property available only in PRO version', $this->plugin_name);?>" ><?php // echo __('Export', $this->plugin_name);?></button></a> -->
    <div class="nav-tab-wrapper">
        <a href="#tab1" class="nav-tab nav-tab-active"><?= __('Results', $this->plugin_name); ?></a>
        <!-- <a href="#tab2" class="nav-tab"><?php // echo __('Statistics', $this->plugin_name); ?></a> -->
    </div>
    <div id="tab1" class="ays-sccp-tab-content ays-sccp-tab-content-active">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="get" id="filter-div" class="alignleft actions bulkactions">
                            <label for="bulk-action-selector-top">Shortcode ID</label>
                            <input type="hidden" name="page" value="secure-copy-content-protection-results-to-view">
                            <select name="orderbyshortcode" id="bulk-action-selector-top">
                                <option value="0" selected><?=__('No Filtering', $this->plugin_name);?></option>
                                <?php
                                    foreach ($this->results_obj->get_sccp_by_id() as $copy_content) {?>
                                    <option value="<?=$copy_content['subscribe_id'];?>" <?=(isset($_REQUEST['orderbyshortcode']) && $_REQUEST['orderbyshortcode'] == $copy_content['subscribe_id']) ? 'selected' : '';?>><?=$copy_content['subscribe_id'];?></option>
                                    <?php }
                                ?>
                            </select>
                            <input type="submit" class="button action" value="<?= __('Filter', $this->plugin_name); ?>" style="width: 3.7rem;">
                        </form>
                        <form method="post">
                            <?php                            
                                $this->results_obj->prepare_items();
                                $this->results_obj->display();
                                $this->results_obj->mark_as_read();
                            ?>
                        </form>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>

    <!-- <div id="tab2" class="ays-poll-tab-content" >
        <a href="https://ays-pro.com/index.php/wordpress/poll-maker/" target="_blank" title="<?php // echo __('This property available only in PRO version', $this->plugin_name);?>">
            <img src="<?php // echo plugins_url() . '/secure-copy-content-protection/admin/images/chart_screen.png';?>" alt="Statistics" style="opacity: 0.5; width:100%" >
        </a>
    </div> -->
</div>
