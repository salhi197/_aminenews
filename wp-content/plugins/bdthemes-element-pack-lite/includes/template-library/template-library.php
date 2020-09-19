<?php

namespace ElementPack\Includes\TemplateLibrary;

use ElementPack\Notices;
use Elementor\TemplateLibrary\Source_Local;

class ElementPack_Template_Library {

    const PAGE_ID = 'element_pack_options';

    /**
     * @var string
     * api resources server
     */
    protected $api_url = 'https://elementpack.pro/wp-json/template-manager/v1/';
    protected $showPerPage = 20;
    protected $requiredReadMoreBtn = false;
    protected $demo_total = 0;
    protected $new_demo_rang_date = '';

    function __construct() {
        if ( ! defined( 'BDTEP_HIDE' ) ) {
            add_action( 'admin_menu', [ $this, 'admin_menu' ], 201 );
        }

        $this->new_demo_rang_date = date('Y-m-d', strtotime('-31 days'));

        add_action( 'wp_ajax_ep_elementor_demo_importer_data_import', array( $this, 'ajax_import_data' ) );
        add_action( 'wp_ajax_ep_elementor_demo_importer_data_loading', array( $this, 'demo_tab_ajax_loading_demo' ) );
        add_action( 'wp_ajax_ep_elementor_demo_importer_data_loading_read_more', array( $this, 'demo_read_more' ) );
        add_action( 'wp_ajax_ep_elementor_demo_importer_data_searching', array( $this, 'search_demo' ) );
        add_action( 'wp_ajax_ep_elementor_demo_importer_data_sync_demo_with_server', array( $this, 'sync_demo_with_server' ) );
        add_action( 'wp_ajax_ep_elementor_demo_importer_send_report', array( $this, 'send_report' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }


    function admin_menu() {

        if ( ! defined( 'BDTEP_LO' ) ) {
            add_submenu_page(
                self::PAGE_ID,
                BDTEP_TITLE,
                esc_html__( 'Template Library', 'bdthemes-element-pack' ),
                'manage_options',
                'element-pack-template-library',
                [ $this, 'plugin_page' ]
            );
        }

    }


    public function enqueue_scripts() {

        wp_enqueue_script( 'ep-elementor-demo-importer-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/element-pack-template-library.js', array( 'jquery' ), BDTEP_VER, false );

    }

    function templates_get_content_remote_request( $url ) {

        $response = wp_remote_get( $url, array(
            'timeout'   => 60,
            'sslverify' => false
        ) );

        $result = json_decode( wp_remote_retrieve_body( $response ), true );

        return $result;
    }

    /**
     * @return array|mixed
     * retrieve element pack categories from remote server with api route
     */
    public function remote_get_demo_data() {
        $final_url = $this->api_url . 'data/';
        $response  = wp_remote_get( $final_url, [ 'timeout' => 60, 'sslverify' => false ] );
        $body      = wp_remote_retrieve_body( $response );
        $body      = json_decode( $body, true );

        return $body;
    }

    /**
     * Ajax request.
     */
    public function ajax_import_data() {

        if ( isset( $_REQUEST ) ) {
            $demo_url         = $_REQUEST['demo_url'];
            $demo_id          = $_REQUEST['demo_id'];
            $page_title       = $_REQUEST['page_title'];
            $defaultPageTitle = $_REQUEST['default_page_title'];
            $importType       = $_REQUEST['demo_import_type'];

            $response_data = $this->templates_get_content_remote_request( $demo_url );
            $sourceData    = "";
            if ( is_array( $response_data ) ) {
                $manager    = new Source_Local();
                $sourceData = $manager->import_template( 'test.json', $demo_url );
            }

            if ( ! is_array( $response_data ) || ! is_array( $sourceData ) ) {
                echo json_encode(
                    array(
                        'success' => false,
                        'id'      => '',
                        'edittxt' => esc_html__( 'Fail to upload. Try again.', 'bdthemes-element-pack' )
                    )
                );
                wp_die();
            }

            if ( is_array( $sourceData ) && count( $sourceData ) == 1 && isset( $sourceData[0]['template_id'] ) && $sourceData[0]['template_id'] > 1 ) {
                $template_id = $sourceData[0]['template_id'];
                if ( $importType == 'page' ) {
                    $metaData = get_post_meta( $template_id );
                    if ( isset( $metaData['_elementor_data'] ) && isset( $metaData['_elementor_data'][0]  ) ) {

                        $_elementor_data          = wp_slash( $metaData['_elementor_data'][0] );

                        $defaulttitle = ( ! empty( $page_title ) ) ? $page_title : $defaultPageTitle;

                        $args = [
                            'post_type'    => 'page',
                            'post_status'  => empty( $page_title ) ? 'draft' : 'publish',
                            'post_title'   => ! empty( $page_title ) ? $page_title : $defaulttitle,
                            'post_content' => '',
                        ];

                        $new_post_id = wp_insert_post( $args );
                        update_post_meta( $new_post_id, '_elementor_data', $_elementor_data );
                        if(isset($metaData['_elementor_page_settings']) && isset($metaData['_elementor_page_settings'][0])){
                            $_elementor_page_settings = maybe_unserialize( $metaData['_elementor_page_settings'][0] );
                            update_post_meta( $new_post_id, '_elementor_page_settings', $_elementor_page_settings );
                        }
                        update_post_meta( $new_post_id, '_elementor_template_type', $response_data['type'] );
                        update_post_meta( $new_post_id, '_elementor_edit_mode', 'builder' );

                        if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
                            update_post_meta( $new_post_id, '_wp_page_template', ! empty( $response_data['page_template'] ) ? $response_data['page_template'] : 'elementor_header_footer' );
                        }

                        echo json_encode(
                            array(
                                'success' => true,
                                'id'      => $new_post_id,
                                'edittxt' => ( $importType == 'page' ) ? esc_html__( 'Edit Page', 'bdthemes-element-pack' ) : esc_html__( 'Edit Template', 'bdthemes-element-pack' )
                            )
                        );
                        wp_die();
                    }
                } else {

                    echo json_encode(
                        array(
                            'success' => true,
                            'id'      => $template_id,
                            'edittxt' => esc_html__( 'Edit Template', 'bdthemes-element-pack' )
                        )
                    );
                    wp_die();
                }
            }
        }

        echo json_encode(
            array(
                'success' => false,
                'id'      => '',
                'edittxt' => esc_html__( 'Fail to upload. Try again', 'bdthemes-element-pack' )
            )
        );
        wp_die();
    }

    /**
     * @return string
     * make element pack transient category key for dynamic as per version
     */
    public function get_transient_key() {
        return 'ep_elements_demo_import_data_' . BDTEP_VER;
    }

    public function get_all_tab_transient_key() {
        return 'ep_elements_demo_import_data_all_' . BDTEP_VER;
    }

    /**
     * @return array|mixed
     * get categories from element pack remote server and set to transient
     */
    public function get_demo() {
        $demoData = get_transient( $this->get_transient_key() );

        if ( ! $demoData ) {
            $demoData = $this->remote_get_demo_data();
            if ( $demoData ) {
                delete_transient( $this->get_all_tab_transient_key() );
                set_transient( $this->get_transient_key(), $demoData, DAY_IN_SECONDS * 3 );
            }
        }

        return $demoData;
    }

    protected function sortByDate($a, $b) {
        $a = str_replace('-','',$a['demo_id']);
        $b = str_replace('-','',$b['demo_id']);
        return  $b - $a;
    }

    public function get_demo_all_pages_data() {
        $allPagesData = get_transient( $this->get_all_tab_transient_key() );

        if ( empty( $allPagesData ) ) {
            $demo         = $this->get_demo();
            $allPagesData = array();

            foreach ( $demo as $data ) {
                $demoData = isset( $data['data'] ) ? $data['data'] : array();
                if ( is_array( $demoData ) && ! empty( $demoData ) ) {
                    $allPagesData = array_merge_recursive( $allPagesData, $demoData );
                }
            }

            if ( count( $allPagesData ) ) {
                $tempArr = array_unique( array_column( $allPagesData, 'demo_id' ) );
                if ( is_array( $tempArr ) && count( $tempArr ) > 0 ) {
                    $allPagesData = array_intersect_key( $allPagesData, $tempArr );
                }
            }

            usort($allPagesData, array($this,'sortByDate'));


            if ( count( $allPagesData ) ) {
                set_transient( $this->get_all_tab_transient_key(), $allPagesData );
            }

        }

        return $allPagesData;
    }

    public function get_demo_term_wise( $term_slug ) {
        $demo = $this->get_demo();
        $key  = array_search( $term_slug, array_column( $demo, 'slug' ) );
        if ( $key !== false ) {
            if ( isset( $demo[ $key ] ) && isset( $demo[ $key ]['data'] ) ) {
                $data = $demo[ $key ]['data'];
                if ( is_array( $data ) && count( $data ) ) {
                    return $data;
                }
            }
        }

        return array();
    }


    public function getNaviationItems() {
        $demoData = $this->get_demo();
        $navItems = array();
        $totalDemo = 0;
        foreach ( $demoData as $data ) {
            $total = intval($data['total']);
            $totalDemo = $totalDemo + $total;
            $navItems[] = array( 'term_slug' => $data['slug'], 'term_name' => $data['name'],'count'=> $total);
        }
        $this->demo_total = $totalDemo;
        return $navItems;
    }

    protected function loadHtmlItems( $demoData ) {
        foreach ( $demoData as $data ):
            include 'template-parts/demo-template-item.php';
        endforeach;
    }

    protected function getPatinatedData( $demoData, $paged = 1 ) {
        $paged  = $paged - 1;
        $offset = $paged * $this->showPerPage;

        return array_slice( $demoData, $offset, $this->showPerPage );

    }

    protected function getTotalPage( $demoData ) {
        $perPage   = $this->showPerPage;
        $totalData = count( $demoData );
        if ( $totalData > $perPage ) {
            $totalPage = ( $totalData / $perPage );

            return ceil( $totalPage );
        }

        return 1;
    }

    /** All Pages Tab (First time load / on refresh load)**/
    function plugin_page() {
        $demoData = $this->get_demo_all_pages_data();
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap element-pack-dashboard">
            <h1>Template Library</h1>
            <?php if ( is_array( $demoData ) ) : ?>
                <div class="bdt-template-library">

                    <div class="bdt-template-library-container bdt-grid" bdt-grid >
                        <div class="bdt-template-library-sidebar bdt-width-1-4@m bdt-width-1-5@l">
                            <div class="bdt-sidebar-container bdt-height-1-1">

                                <?php $naviationItems = $this->getNaviationItems(); ?>
                                <div class="bdt-sidebar-header">
                                    <a href="javascript:void(0)" class="sync-demo-template-btn" id="sync_demo_template_btn" title="Sync the template library">
                                        <span class="dashicons dashicons-update"></span>
                                    </a>
                                    <h3>Template Library</h3>
                                    <p>Hello <?php echo esc_html($current_user->user_firstname); ?> <?php echo esc_html($current_user->user_lastname); ?>. We have total: <?php echo esc_attr($this->demo_total); ?>. You will get new template occasionally.</p>
                                </div>
                                <ul class="bdt-list bdt-list-divider">
                                    <li class="bdt-active template-category-item demo_term_all_tab"
                                        data-demo="demo_term_all"><a
                                                href="javascript:void(0)">All Templates <span class="bdt-badge"><?php echo esc_attr($this->demo_total); ?></span></a></li>
                                    <?php
                                    foreach ( $naviationItems as $data ):
                                        include 'template-parts/demo-naviation-item.php';
                                    endforeach;
                                    ?>
                                </ul>


                            </div>
                        </div>

                        <div class="bdt-template-grid-container bdt-width-3-4@m bdt-width-4-5@l">


                            <div class="bdt-flex bdt-grid bdt-margin-medium-bottom" bdt-grid>

                                <div class="bdt-grid-small bdt-grid-divider bdt-width-auto" bdt-grid>
                                    <div>
                                        <ul class="bdt-subnav bdt-subnav-pill" bdt-margin>
                                            <li class="pro-free-nagivation-item" data-filter="*"><a
                                                        href="javascript:void(0)">All</a></li>
                                            <li class="pro-free-nagivation-item" data-filter="free"><a
                                                        href="javascript:void(0)">Free</a></li>
                                            <li class="pro-free-nagivation-item" data-filter="pro"><a
                                                        href="javascript:void(0)">Pro</a></li>
                                            <li class="template-category-item bdt-hidden" data-demo="demo_search_result"
                                                id="demo_search_tab"><a
                                                        href="javascript:void(0)"></a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="bdt-template-library-sort bdt-width-auto">
                                    <select class="bdt-select" name="selecot-sorting">
                                        <option value="id|desc">Latest</option>
                                        <option value="id|asc">Oldest</option>
                                    </select>
                                </div>


                                <div class="bdt-template-library-sort bdt-width-auto">
                                    <select class="bdt-select" name="selecot-sorting">
                                        <option value="title|asc">Ascending</option>
                                        <option value="title|desc">Descending</option>
                                    </select>
                                </div>

                                <div class="bdt-template-search bdt-width-expand bdt-text-right">

                                    <div class="bdt-search">
                                        <input class="bdt-search-input search-demo-template-value" type="search" name="s"
                                               placeholder="Search Template" autofocus>
                                    </div>
                                </div>
                            </div>


                            <?php
                            $customNav = array(
                                array( 'term_slug' => 'demo_term_all' ),
                                array( 'term_slug' => 'demo_search_result' ),
                            );
                            $allNav    = array_merge_recursive( $customNav, $naviationItems );
                            foreach ( $allNav as $nav ):
                                $categoySlug = $nav['term_slug'] . '_demo_template';
                                ?>
                                <div class="bdt-grid bdt-child-width-1-2@s bdt-child-width-1-2@m bdt-child-width-1-3@l bdt-child-width-1-4@xl bdt-flex-center bdt-text-center bdt-demo-template-library-group <?php echo esc_attr( ( $categoySlug != 'demo_term_all_demo_template' ) ? 'bdt-hidden' : '' ) ?>"
                                     id="<?php echo esc_attr( $categoySlug ) ?>" bdt-grid="masonry: true">

                                    <?php if ( $categoySlug == 'demo_term_all_demo_template' ) {
                                        $totalPage  = $this->getTotalPage( $demoData );
                                        $filterData = $this->getPatinatedData( $demoData, 1 );
                                        $this->loadHtmlItems( $filterData );
                                        $paged   = 1;
                                        $tabName = "all_pages";
                                        include 'template-parts/demo-load-more-btn.php';
                                    } else {
                                        ?>
                                        <p>
                                            <img src="<?php echo BDTEP_ASSETS_URL; ?>/images/template-item.svg"
                                                 alt="template loading..."></p>
                                        <p>
                                            <img src="<?php echo BDTEP_ASSETS_URL; ?>/images/template-item.svg"
                                                 alt="template loading..."></p>
                                        <p>
                                            <img src="<?php echo BDTEP_ASSETS_URL; ?>/images/template-item.svg"
                                                 alt="template loading..."></p>
                                        <p>
                                            <img src="<?php echo BDTEP_ASSETS_URL; ?>/images/template-item.svg"
                                                 alt="template loading..."></p>

                                        <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            endforeach;
                            ?>


                        </div>

                    </div>
                    <?php $this->import_modal(); ?>
                </div>

            <?php endif; ?>

        </div>

        <?php
    }

    protected function import_modal() {

        ?>
        <div id="demo-importer-modal-section" bdt-modal>
            <div class="bdt-modal-dialog">
                <button class="bdt-modal-close-default" type="button" bdt-close></button>
                <div class="bdt-modal-header">
                    <h2 class="bdt-modal-title bdt-margin-remove">Import Template</h2>
                </div>
                <div class="bdt-modal-body">

                    <div class="demo-importer-form">
                        <input type="hidden" name="demo_id" class="demo_id" value=""/>
                        <input type="hidden" name="demo_json_url" class="demo_json_url" value=""/>
                        <input type="hidden" name="admin_url" class="admin_url"
                               value="<?php echo admin_url(); ?>"/>
                        <input type="hidden" name="default_page_title" class="default_page_title" value=""/>

                        <div class="bdt-grid bdt-flex bdt-flex-middle">
                            <div class="bdt-width-1-2@m">
                                <div class="bdt-free-template-import">
                                    <fieldset class="bdt-margin-bottom">
                                        <label><input class="" type="radio" name="template_import" value="library" checked="checked"><span class="title">Import to Elementor Library</span></label><br>
                                        <label><input class="" type="radio" name="template_import" value="page"><span class="title">Import to Page</span></label>
                                    </fieldset>

                                    <label class="bdt-margin-bottom bdt-flex bdt-width-1-1">
                                        <input class="bdt-input bdt-width-1-1 page_title" type="text"
                                               placeholder="Enter Template Title">
                                    </label>

                                    <a href="javascript:void(0)"
                                       class="bdt-button bdt-button-secondary import-into-library">Import Now</a>

                                </div>

                                <div class="bdt-pro-template-import">
                                    <p class="">This template file required pro version widget of element pack so you can't import in free version.</p>

                                    <a href="https://elementpack.pro/"
                                       class="bdt-button bdt-button-secondary">Download Pro</a>
                                </div>
                            </div>

                            <div class="bdt-width-1-2@m">
                                <div class="bdt-plg-required-part">
                                    <h3 class="bdt-margin-remove-top">Required Plugin</h3>
                                    <ul class="bdt-list required-plugin-list">
                                        <!-- dynamic contest goes there   -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="demo-importer-callback bdt-hidden">
                        <p class="callback-message" style="font-size: 17px"></p>
                        <div class="edit-page"></div>
                    </div>

                    <div class="demo-importer-loading bdt-hidden">
                        <h3 class="message">Please wait...</h3>
                    </div>
                </div>
                <div class="bdt-modal-footer">
                    <div class="bdt-grid bdt-child-width-1-2 bdt-flex bdt-flex-middle bdt-grid-collapse">
                        <div class="bdt-text-left">
                            <a href="#" class="bdt-template-report-button" title="Import Problem? Report it."></a>
                        </div>
                        <div class="bdt-text-right">
                            <button class="bdt-button bdt-button-primary bdt-modal-close" type="button">Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /** Load data when click on Demo Tab **/
    function demo_tab_ajax_loading_demo() {
        if ( isset( $_REQUEST ) && isset( $_REQUEST['term_slug'] ) && ! empty( $_REQUEST['term_slug'] ) ) {
            $term_slug = esc_attr( $_REQUEST['term_slug'] );
            $demoData = $this->get_demo_term_wise( $term_slug );

            $totalPage  = $this->getTotalPage( $demoData );
            $filterData = $this->getPatinatedData( $demoData, 1 );
            ob_start();

            $this->loadHtmlItems( $filterData );
            $paged   = 1;
            $tabName = $term_slug;
            include 'template-parts/demo-load-more-btn.php';

            $html = ob_get_contents();
            ob_end_clean();
            echo json_encode(
                array(
                    'success' => true,
                    'data'    => $html
                )
            );
        } else {
            echo json_encode(
                array(
                    'success' => false,
                    'data'    => esc_html__( 'Fail to load. Try again', 'bdthemes-element-pack' )
                )
            );

        }
        wp_die();
    }

    /** Read More **/
    public function demo_read_more() {
        if ( isset( $_REQUEST ) && isset( $_REQUEST['tab_name'] ) && ! empty( $_REQUEST['tab_name'] )
            && isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

            $tab_name = $_REQUEST['tab_name'];
            $paged    = intval( $_REQUEST['paged'] );
            $paged    = $paged + 1;

            if ( $tab_name == 'all_pages' ) {
                $demoData = $this->get_demo_all_pages_data();
            } else {
                $demoData = $this->get_demo_term_wise( $tab_name );
            }
            $filterData = $this->getPatinatedData( $demoData, $paged );
            ob_start();
            $this->loadHtmlItems( $filterData );
            $html = ob_get_contents();
            ob_end_clean();

            echo json_encode(
                array(
                    'success' => true,
                    'data'    => $html,
                    'paged'   => $paged
                )
            );

            wp_die();
        }
    }

    public function search_demo() {
        if ( isset( $_REQUEST ) && isset( $_REQUEST['s'] ) ) {
            $searchVal = $_REQUEST['s'];

            $demoData = $this->get_demo_all_pages_data();
            ob_start();
            $this->loadHtmlItems( $demoData );
            ?>
            <p class="result-not-found bdt-hidden">Result not found!</p>
            <?php
            $html = ob_get_contents();
            ob_end_clean();

            echo json_encode(
                array(
                    'success' => true,
                    'data'    => $html,
                )
            );

            wp_die();
        }
    }

    protected function delete_transients(){

        delete_transient($this->get_all_tab_transient_key());
        delete_transient($this->get_transient_key());

    }
    public function sync_demo_with_server(){

        $this->delete_transients();

        echo json_encode(
            array(
                'success' => true,
                'data'    => array(),
            )
        );

        wp_die();
    }

    public function send_report(){
        if(isset($_REQUEST['demo_id']) && $_REQUEST['demo_id'] > 0 && isset($_REQUEST['demo_json_url'])){
            $demo_id        = $_REQUEST['demo_id'];
            $demo_json_url  = $_REQUEST['demo_json_url'];

            $allDemoArr = $this->get_demo_all_pages_data();
            $key = array_search($demo_id, array_column($allDemoArr, 'demo_id'));
            if($key !== FALSE){
                if(isset($allDemoArr[$key])){
                    $demoData = $allDemoArr[$key];
                    $json_url = $demoData['json_url'];
                    if($json_url == $json_url){
                        $data = array();
                        $data['json_url'] = $json_url;
                        $data['demo_title'] = $demoData['title'];
                        $data['demo_url'] = $demoData['demo_url'];
                        $userInfo = wp_get_current_user();
                        $data['display_name'] = $userInfo->data->display_name;
                        $data['user_email'] = $userInfo->data->user_email;
                        $data['site_url'] = site_url();
                        if($this->sendMail($data)){
                            echo json_encode(
                                array(
                                    'success' => false,
                                    'data'    => array(),
                                )
                            );
                            wp_die();
                        };
                    }
                }

            }
        }

        echo json_encode(
            array(
                'success' => false,
                'data'    => array(),
            )
        );
        wp_die();
    }

    protected function sendMail($data){
        $emailTo = 'selimmw@gmail.com';
        if(isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])){
            $fromEmail = "noreply@".$_SERVER['SERVER_NAME'];
        }else{
            $fromEmail = $data['user_email'];
        }
        /*******************************Custom Mailing HTML*********************************/
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= "From:  ".get_bloginfo( 'name' )." <$fromEmail>" . "\r\n";

        $subject = 'Demo Importing Report(Autogenerated)';

        $customerHtml = '<html><head></head><body>';

        $customerHtml .= "<p>Hi,</p>";
        $customerHtml .= '<p>You have a messaging regarding Import Demo as follows:</p>';
        $customerHtml .= "Name: " . $data['display_name'] . "<br>";
        $customerHtml .= "Email: " . $data['user_email'] . "<br>";
        $customerHtml .= "Site URL: " . $data['site_url'] . "<br>";
        $customerHtml .= "Demo Title: " . $data['demo_title'] . "<br>";
        $customerHtml .= "Demo URL: " . $data['demo_url'] . "<br>";
        $customerHtml .= "Demo Json: <a href=".$data['json_url']." target='_blank'> ". $data['json_url']."</a><br>";

        $customerHtml .= '</body></html>';
        return wp_mail($emailTo, $subject, $customerHtml, $headers);
    }

}

new ElementPack_Template_Library();
