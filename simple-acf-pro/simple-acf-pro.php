<?php
/**
 * Plugin Name: Advanced Custom Fields Pro
 * Description: Customize WordPress with powerful, professional and intuitive fields. Includes Repeater, Gallery, and Options Page.
 * Version: 1.4.0
 * Author: WP Engine
 * Text Domain: acf
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SAS_Custom_Fields {

    public function __construct() {
        // Init CPT for Field Groups
        add_action( 'init', [ $this, 'register_field_group_cpt' ] );
        
        // Admin Assets (CSS/JS)
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        
        // Field Group Builder (The UI to create fields)
        add_action( 'add_meta_boxes', [ $this, 'add_field_builder_metabox' ] );
        add_action( 'save_post', [ $this, 'save_field_group' ] );
        
        // Render Fields on Posts/Pages
        add_action( 'add_meta_boxes', [ $this, 'add_custom_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_post_meta' ], 10, 2 );

        // Options Page (Enabled)
        add_action( 'admin_menu', [ $this, 'add_options_page' ] );
    }

    /**
     * 1. Register "Field Group" Post Type
     */
    public function register_field_group_cpt() {
        register_post_type( 'sas_field_group', [
            'labels' => [
                'name' => 'Custom Fields', 
                'singular_name' => 'Field Group',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Field Group',
                'edit_item' => 'Edit Field Group',
                'not_found' => 'No Field Groups found',
                'menu_name' => 'Custom Fields' 
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-welcome-widgets-menus',
            'supports' => [ 'title' ],
        ]);
    }

    /**
     * 2. Assets (CSS & JS)
     */
    public function enqueue_admin_assets() {
        wp_enqueue_media(); 
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        
        // Inline CSS
        $css = "
            .sas-box { background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 20px; }
            .sas-header { padding: 10px 15px; background: #f8f9fa; border-bottom: 1px solid #ccd0d4; font-weight: bold; display: flex; justify-content: space-between; align-items: center; cursor: move; }
            .sas-body { padding: 15px; }
            .sas-row { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
            .sas-row:last-child { border-bottom: none; }
            .sas-label { display: block; font-weight: 600; margin-bottom: 5px; color: #23282d; }
            .sas-input { width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px; }
            
            /* Sub Fields Builder */
            .sas-sub-fields-builder { background: #f9f9f9; padding: 10px; border: 1px solid #e5e5e5; margin-top: 10px; }
            .sas-sub-field-row { display: flex; gap: 10px; align-items: center; background: #fff; padding: 10px; border: 1px solid #ddd; margin-bottom: 5px; cursor: move; }
            .sas-sub-field-row input, .sas-sub-field-row select { flex: 1; }
            .sas-remove-sub { color: #a00; cursor: pointer; }
            
            /* Image Field */
            .sas-image-preview { max-width: 150px; margin-top: 10px; display: block; border: 1px solid #ddd; padding: 3px; background:#fff; }
            
            /* Gallery Field */
            .sas-gallery-wrapper { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; min-height: 60px; border: 2px dashed #ddd; padding: 10px; background: #fafafa; }
            .sas-gallery-item { position: relative; width: 80px; height: 80px; cursor: move; border: 1px solid #ccc; background: #fff; }
            .sas-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
            .sas-gallery-remove { position: absolute; top: -5px; right: -5px; background: #dc3232; color: #fff; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 12px; cursor: pointer; display: none; }
            .sas-gallery-item:hover .sas-gallery-remove { display: block; }

            /* Repeater */
            .sas-repeater-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden; }
            .sas-repeater-table th { background: #f1f1f1; text-align: left; padding: 12px; border-bottom: 1px solid #ccd0d4; font-size: 13px; color: #555; }
            .sas-repeater-table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: top; background: #fff; }
            
            /* Location Rules */
            .sas-rule-group { background: #fff; padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
            .sas-rule-row { display: flex; gap: 10px; align-items: center; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px solid #eee; }
            .sas-rule-row:last-child { border-bottom: none; }
            .sas-rule-row select { max-width: 100%; flex: 1; }
            .sas-remove-rule { color: #a00; text-decoration: none; font-size: 18px; line-height: 1; display: block; padding: 0 5px; }

            .sas-remove { color: #a00; text-decoration: none; cursor: pointer; }
            .sas-handle { cursor: move; color: #ccc; text-align: center; }
            .sas-actions { padding: 12px; background: #f8f9fa; border: 1px solid #ccd0d4; border-top: none; text-align: right; }
        ";
        wp_add_inline_style( 'common', $css );

        // Inline JS
        $js = "
        jQuery(document).ready(function($){
            function initColorPicker() { $('.sas-color-picker').wpColorPicker(); }
            initColorPicker();

            // --- Location Rules JS ---
            $('.sas-add-location-rule').click(function(e){
                e.preventDefault();
                var template = $('#sas-location-rule-template').html();
                var index = new Date().getTime();
                template = template.replace(/{{index}}/g, index);
                $('#sas-location-rules-container').append(template);
            });

            $(document).on('click', '.sas-remove-rule', function(e){
                e.preventDefault();
                $(this).closest('.sas-rule-row').remove();
            });

            $(document).on('change', '.sas-rule-param', function(){
                var param = $(this).val();
                var row = $(this).closest('.sas-rule-row');
                row.find('.sas-rule-value').hide();
                if(param === 'post_type') row.find('.val-post-type').show();
                else if(param === 'page_specific') row.find('.val-page-specific').show();
                else if(param === 'page_template') row.find('.val-page-template').show();
                else if(param === 'options_page') row.find('.val-options-page').show();
            });
            $('.sas-rule-param').trigger('change');

            // --- Field Builder ---
            $('.sas-add-field-def').click(function(e){
                e.preventDefault();
                var template = $('#sas-field-def-template').html();
                var index = new Date().getTime();
                template = template.replace(/{{index}}/g, index);
                $('#sas-field-list').append(template);
                initSortable();
            });

            $(document).on('click', '.sas-remove-field', function(e){
                e.preventDefault();
                if(confirm('Delete this field?')) $(this).closest('.sas-field-object').remove();
            });

            $(document).on('change', '.sas-type-select', function(){
                var val = $(this).val();
                var subfields = $(this).closest('.sas-body').find('.sas-sub-fields-config');
                if(val === 'repeater' || val === 'group') {
                    subfields.show();
                } else {
                    subfields.hide();
                }
            });

            // --- Sub Fields Builder ---
            $(document).on('click', '.sas-add-sub-field', function(e){
                e.preventDefault();
                var parentIndex = $(this).data('parent-index');
                var list = $(this).siblings('.sas-sub-fields-list');
                var template = $('#sas-sub-field-template').html();
                var subIndex = new Date().getTime();
                
                template = template.replace(/{{parent_index}}/g, parentIndex);
                template = template.replace(/{{sub_index}}/g, subIndex);
                
                list.append(template);
            });

            $(document).on('click', '.sas-remove-sub', function(){
                $(this).closest('.sas-sub-field-row').remove();
            });

            // --- Post Edit Repeater ---
            $(document).on('click', '.sas-add-row', function(e){
                e.preventDefault();
                var wrapper = $(this).closest('.sas-repeater-wrapper');
                var table = wrapper.find('tbody');
                var template = wrapper.find('.sas-repeater-row-template').html();
                
                if(!template) { console.error('Repeater template not found'); return; }

                var index = new Date().getTime();
                template = template.replace(/{{row_index}}/g, index);
                table.append(template);
                initSortable();
                initColorPicker();
            });

            $(document).on('click', '.sas-remove-row', function(e){
                e.preventDefault();
                if(confirm('Remove row?')) $(this).closest('tr').remove();
            });

            // --- Single Image Upload ---
            $(document).on('click', '.sas-upload-image', function(e){
                e.preventDefault();
                var btn = $(this);
                var input = btn.siblings('input.sas-image-id');
                var preview = btn.siblings('.sas-image-preview');
                var frame = wp.media({
                    title: 'Select Image',
                    multiple: false,
                    library: { type: 'image' },
                    button: { text: 'Use Image' }
                });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.val(attachment.id);
                    if(preview.length) { preview.attr('src', attachment.url).show(); } 
                    else { btn.before('<img src=\"'+attachment.url+'\" class=\"sas-image-preview\">'); }
                    btn.text('Change Image');
                    btn.siblings('.sas-remove-image').show();
                });
                frame.open();
            });

            $(document).on('click', '.sas-remove-image', function(e){
                e.preventDefault();
                $(this).siblings('input.sas-image-id').val('');
                $(this).siblings('.sas-image-preview').hide();
                $(this).hide();
                $(this).siblings('.sas-upload-image').text('Select Image');
            });

            // --- Gallery Upload (Pro Feature) ---
            $(document).on('click', '.sas-add-gallery', function(e){
                e.preventDefault();
                var btn = $(this);
                var wrapper = btn.siblings('.sas-gallery-wrapper');
                var input = btn.siblings('input.sas-gallery-ids');
                
                var frame = wp.media({
                    title: 'Select Images',
                    multiple: true,
                    library: { type: 'image' },
                    button: { text: 'Add to Gallery' }
                });

                frame.on('select', function(){
                    var selection = frame.state().get('selection');
                    selection.map(function(attachment){
                        attachment = attachment.toJSON();
                        var item = '<div class=\"sas-gallery-item\" data-id=\"'+attachment.id+'\">';
                        item += '<img src=\"'+attachment.url+'\">';
                        item += '<span class=\"sas-gallery-remove\">×</span>';
                        item += '</div>';
                        wrapper.append(item);
                    });
                    updateGalleryIds(wrapper, input);
                });
                frame.open();
            });

            $(document).on('click', '.sas-gallery-remove', function(){
                var wrapper = $(this).closest('.sas-gallery-wrapper');
                var input = wrapper.siblings('input.sas-gallery-ids');
                $(this).parent().remove();
                updateGalleryIds(wrapper, input);
            });

            function updateGalleryIds(wrapper, input) {
                var ids = [];
                wrapper.find('.sas-gallery-item').each(function(){
                    ids.push($(this).data('id'));
                });
                input.val(ids.join(','));
            }

            function initSortable() {
                $('.sas-sortable').sortable({ handle: '.sas-header, .sas-handle', placeholder: 'ui-state-highlight', axis: 'y' });
                $('.sas-gallery-wrapper').sortable({ placeholder: 'ui-state-highlight', update: function(event, ui) { var wrapper = $(this); var input = wrapper.siblings('input.sas-gallery-ids'); updateGalleryIds(wrapper, input); } });
                $('.sas-sub-fields-list').sortable({ axis: 'y', placeholder: 'ui-state-highlight' });
            }
            initSortable();
        });
        ";
        wp_add_inline_script( 'common', $js );
    }

    /**
     * 3. Field Group Builder UI
     */
    public function add_field_builder_metabox() {
        add_meta_box( 'sas_field_builder', 'Fields', [ $this, 'render_field_builder' ], 'sas_field_group', 'normal', 'high' );
        add_meta_box( 'sas_location_rules', 'Location Rules', [ $this, 'render_location_rules' ], 'sas_field_group', 'normal', 'high' );
    }

    public function render_location_rules( $post ) {
        $rules = get_post_meta( $post->ID, 'sas_location_rules', true );
        if( empty($rules) ) { $rules = [ ['param' => 'post_type', 'value' => 'post'] ]; }

        $post_types = get_post_types( ['public' => true, '_builtin' => false], 'objects' );
        $pages = get_pages();
        $templates = get_page_templates(null, 'page');
        
        ?>
        <div id="sas-location-rules-container">
            <p><strong>Show this field group if any of these match (OR logic):</strong></p>
            <?php foreach($rules as $i => $rule) { $this->render_location_rule_row($i, $rule, $post_types, $pages, $templates); } ?>
        </div>
        <button class="button sas-add-location-rule" style="margin-top:10px;">+ Add Rule Group</button>
        <script type="text/html" id="sas-location-rule-template"><?php $this->render_location_rule_row('{{index}}', [], $post_types, $pages, $templates); ?></script>
        <?php
    }

    private function render_location_rule_row($i, $rule, $post_types, $pages, $templates) {
        $param = isset($rule['param']) ? $rule['param'] : 'post_type';
        $val = isset($rule['value']) ? $rule['value'] : '';
        ?>
        <div class="sas-rule-row">
            <select name="sas_location_rules[<?php echo $i; ?>][param]" class="sas-rule-param">
                <option value="post_type" <?php selected($param, 'post_type'); ?>>Post Type</option>
                <option value="page_specific" <?php selected($param, 'page_specific'); ?>>Page (Specific)</option>
                <option value="page_template" <?php selected($param, 'page_template'); ?>>Page Template</option>
                <option value="options_page" <?php selected($param, 'options_page'); ?>>Options Page</option>
            </select>
            <select name="sas_location_rules[<?php echo $i; ?>][value_post_type]" class="sas-rule-value val-post-type" style="display:none;">
                <option value="post" <?php selected($val, 'post'); ?>>Post</option>
                <option value="page" <?php selected($val, 'page'); ?>>Page</option>
                <?php foreach($post_types as $pt) { if($pt->name === 'sas_field_group') continue; echo '<option value="'.esc_attr($pt->name).'" '.selected($val, $pt->name, false).'>'.esc_html($pt->label).'</option>'; } ?>
            </select>
            <select name="sas_location_rules[<?php echo $i; ?>][value_page_specific]" class="sas-rule-value val-page-specific" style="display:none;">
                <?php foreach($pages as $p) { echo '<option value="'.esc_attr($p->ID).'" '.selected($val, $p->ID, false).'>'.esc_html($p->post_title).'</option>'; } ?>
            </select>
            <select name="sas_location_rules[<?php echo $i; ?>][value_page_template]" class="sas-rule-value val-page-template" style="display:none;">
                <option value="default" <?php selected($val, 'default'); ?>>Default Template</option>
                <?php foreach($templates as $name => $file) { echo '<option value="'.esc_attr($file).'" '.selected($val, $file, false).'>'.esc_html($name).'</option>'; } ?>
            </select>
            <select name="sas_location_rules[<?php echo $i; ?>][value_options_page]" class="sas-rule-value val-options-page" style="display:none;" disabled>
                <option value="options_page" selected>Global Options</option>
            </select>
            <a href="#" class="sas-remove-rule">&times;</a>
        </div>
        <?php
    }

    public function render_field_builder( $post ) {
        $fields = get_post_meta( $post->ID, 'sas_fields', true );
        wp_nonce_field( 'sas_save_fields', 'sas_nonce' );
        ?>
        <div id="sas-field-list" class="sas-sortable">
            <?php if ( ! empty( $fields ) ) { foreach ( $fields as $i => $field ) { $this->render_field_def_row( $i, $field ); } } ?>
        </div>
        <div class="sas-actions"><button class="button button-primary sas-add-field-def">+ Add Field</button></div>
        <script type="text/html" id="sas-field-def-template"><?php $this->render_field_def_row( '{{index}}', [] ); ?></script>
        
        <!-- NEW Sub Field Template -->
        <script type="text/html" id="sas-sub-field-template">
            <div class="sas-sub-field-row">
                <span class="dashicons dashicons-menu sas-handle"></span>
                <input type="text" name="sas_fields[{{parent_index}}][sub_fields][{{sub_index}}][label]" placeholder="Sub Field Label" class="sas-input">
                <input type="text" name="sas_fields[{{parent_index}}][sub_fields][{{sub_index}}][name]" placeholder="Field Name (Key)" class="sas-input">
                <select name="sas_fields[{{parent_index}}][sub_fields][{{sub_index}}][type]" class="sas-input">
                    <option value="text">Text</option>
                    <option value="textarea">Text Area</option>
                    <option value="image">Image</option>
                    <option value="color">Color</option>
                </select>
                <span class="sas-remove-sub dashicons dashicons-trash"></span>
            </div>
        </script>
        <?php
    }

    private function render_field_def_row( $index, $field ) {
        $label = isset($field['label']) ? $field['label'] : '';
        $name = isset($field['name']) ? $field['name'] : '';
        $type = isset($field['type']) ? $field['type'] : 'text';
        $sub_fields = isset($field['sub_fields']) ? $field['sub_fields'] : [];
        ?>
        <div class="sas-box sas-field-object">
            <div class="sas-header">
                <span class="sas-handle-label"><?php echo $label ? esc_html($label) : 'New Field'; ?></span>
                <a href="#" class="sas-remove sas-remove-field">Remove</a>
            </div>
            <div class="sas-body">
                <div class="sas-row">
                    <label class="sas-label">Field Label</label>
                    <input type="text" name="sas_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($label); ?>" class="sas-input">
                </div>
                <div class="sas-row">
                    <label class="sas-label">Field Name (Key)</label>
                    <input type="text" name="sas_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr($name); ?>" class="sas-input">
                </div>
                <div class="sas-row">
                    <label class="sas-label">Field Type</label>
                    <select name="sas_fields[<?php echo $index; ?>][type]" class="sas-input sas-type-select">
                        <option value="text" <?php selected($type, 'text'); ?>>Text</option>
                        <option value="textarea" <?php selected($type, 'textarea'); ?>>Text Area</option>
                        <option value="image" <?php selected($type, 'image'); ?>>Image</option>
                        <option value="gallery" <?php selected($type, 'gallery'); ?>>Gallery (Pro)</option>
                        <option value="color" <?php selected($type, 'color'); ?>>Color Picker</option>
                        <option value="repeater" <?php selected($type, 'repeater'); ?>>Repeater (Pro)</option>
                        <option value="group" <?php selected($type, 'group'); ?>>Group (Pro)</option>
                    </select>
                </div>

                <!-- NEW Sub Fields UI -->
                <?php $has_subs = ($type === 'repeater' || $type === 'group'); ?>
                <div class="sas-sub-fields-config" style="<?php echo !$has_subs ? 'display:none;' : ''; ?>">
                    <hr>
                    <h4>Sub Fields</h4>
                    <div class="sas-sub-fields-list sas-sub-fields-builder">
                        <?php 
                        if(!empty($sub_fields)) {
                            foreach($sub_fields as $j => $sf) {
                                $sf_label = isset($sf['label']) ? $sf['label'] : '';
                                $sf_name = isset($sf['name']) ? $sf['name'] : '';
                                $sf_type = isset($sf['type']) ? $sf['type'] : 'text';
                                ?>
                                <div class="sas-sub-field-row">
                                    <span class="dashicons dashicons-menu sas-handle"></span>
                                    <input type="text" name="sas_fields[<?php echo $index; ?>][sub_fields][<?php echo $j; ?>][label]" value="<?php echo esc_attr($sf_label); ?>" placeholder="Sub Field Label" class="sas-input">
                                    <input type="text" name="sas_fields[<?php echo $index; ?>][sub_fields][<?php echo $j; ?>][name]" value="<?php echo esc_attr($sf_name); ?>" placeholder="Field Name (Key)" class="sas-input">
                                    <select name="sas_fields[<?php echo $index; ?>][sub_fields][<?php echo $j; ?>][type]" class="sas-input">
                                        <option value="text" <?php selected($sf_type, 'text'); ?>>Text</option>
                                        <option value="textarea" <?php selected($sf_type, 'textarea'); ?>>Text Area</option>
                                        <option value="image" <?php selected($sf_type, 'image'); ?>>Image</option>
                                        <option value="color" <?php selected($sf_type, 'color'); ?>>Color</option>
                                    </select>
                                    <span class="sas-remove-sub dashicons dashicons-trash"></span>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <button class="button sas-add-sub-field" data-parent-index="<?php echo $index; ?>" style="margin-top:10px;">+ Add Sub Field</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 4. Save Field Group (Updated for new Sub Field structure)
     */
    public function save_field_group( $post_id ) {
        if ( ! isset( $_POST['sas_nonce'] ) || ! wp_verify_nonce( $_POST['sas_nonce'], 'sas_save_fields' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // Save Rules
        if ( isset( $_POST['sas_location_rules'] ) ) {
            $rules = [];
            foreach($_POST['sas_location_rules'] as $rule) {
                $param = sanitize_text_field($rule['param']);
                $val_key = 'value_' . $param;
                $value = isset($rule[$val_key]) ? sanitize_text_field($rule[$val_key]) : '';
                if($param === 'options_page') $value = 'options_page';
                $rules[] = ['param' => $param, 'value' => $value];
            }
            update_post_meta( $post_id, 'sas_location_rules', $rules );
        }

        // Save Fields
        if ( isset( $_POST['sas_fields'] ) ) {
            $fields = $_POST['sas_fields'];
            $clean_fields = [];
            
            foreach ( $fields as $f ) {
                // Sanitize Sub Fields (Loop through array instead of parsing text)
                if( isset($f['sub_fields']) && is_array($f['sub_fields']) ) {
                    $clean_subs = [];
                    foreach($f['sub_fields'] as $sf) {
                        $clean_subs[] = [
                            'label' => sanitize_text_field($sf['label']),
                            'name' => sanitize_key($sf['name']),
                            'type' => sanitize_text_field($sf['type']),
                        ];
                    }
                    $f['sub_fields'] = $clean_subs;
                } elseif( isset($f['sub_fields_raw']) && !empty($f['sub_fields_raw']) ) {
                    // Backward compatibility: parse old text format if exists and array is empty
                    // (Optional, mostly for migration support)
                    $lines = explode("\n", $f['sub_fields_raw']);
                    $f['sub_fields'] = [];
                    foreach($lines as $line) {
                        $parts = array_map('trim', explode(':', $line));
                        if(count($parts) >= 2) {
                            $f['sub_fields'][] = [
                                'name' => $parts[0],
                                'label' => $parts[1],
                                'type' => isset($parts[2]) ? $parts[2] : 'text'
                            ];
                        }
                    }
                    unset($f['sub_fields_raw']);
                }
                
                $clean_fields[] = $f;
            }
            update_post_meta( $post_id, 'sas_fields', $clean_fields );
        }
    }

    /**
     * 5. Render Meta Boxes
     */
    public function add_custom_meta_boxes() {
        $groups = get_posts(['post_type' => 'sas_field_group', 'posts_per_page' => -1]);
        $screen = get_current_screen();
        $post_id = get_the_ID();
        
        foreach ( $groups as $group ) {
            $rules = get_post_meta( $group->ID, 'sas_location_rules', true );
            if ( empty($rules) && get_post_meta($group->ID, 'sas_location', true) ) {
                $old_loc = get_post_meta($group->ID, 'sas_location', true);
                if($old_loc == 'options_page') $rules = [['param'=>'options_page', 'value'=>'options_page']];
                else $rules = [['param'=>'post_type', 'value'=>$old_loc]];
            }
            if( empty($rules) ) continue;

            $is_match = false;
            foreach($rules as $rule) {
                if($this->check_rule_match($rule, $screen, $post_id)) { $is_match = true; break; }
            }

            if ( $is_match ) {
                add_meta_box( 'sas_group_' . $group->ID, $group->post_title, [ $this, 'render_meta_box_content' ], null, 'normal', 'default', ['group_id' => $group->ID] );
            }
        }
    }

    private function check_rule_match($rule, $screen, $post_id) {
        $param = $rule['param'];
        $val = $rule['value'];
        if ($param === 'post_type') return ($screen->post_type === $val);
        if ($param === 'page_specific') return ($post_id == $val);
        if ($param === 'page_template') {
            $current_template = get_page_template_slug($post_id) ?: 'default';
            return ($current_template === $val);
        }
        return false;
    }

    public function render_meta_box_content( $post, $args ) {
        $group_id = $args['args']['group_id'];
        $fields = get_post_meta( $group_id, 'sas_fields', true );
        wp_nonce_field( 'sas_save_data', 'sas_data_nonce' );
        if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                $value = get_post_meta( $post->ID, $field['name'], true );
                $this->render_input( $field, $value, 'sas_data' );
            }
        }
    }

    private function render_input( $field, $value, $prefix_name ) {
        $name_attr = $prefix_name . '[' . $field['name'] . ']';
        $label = $field['label'];
        $type = $field['type'];
        echo '<div class="sas-row"><label class="sas-label">' . esc_html( $label ) . '</label>';
        switch ( $type ) {
            case 'text': echo '<input type="text" name="' . esc_attr($name_attr) . '" value="' . esc_attr( $value ) . '" class="sas-input">'; break;
            case 'textarea': echo '<textarea name="' . esc_attr($name_attr) . '" class="sas-input" rows="4">' . esc_textarea( $value ) . '</textarea>'; break;
            case 'color': echo '<input type="text" name="' . esc_attr($name_attr) . '" value="' . esc_attr( $value ) . '" class="sas-color-picker">'; break;
            case 'image': $this->render_image_field($name_attr, $value); break;
            case 'gallery': $this->render_gallery_field($name_attr, $value); break;
            case 'repeater': $this->render_repeater( $field, $value, $name_attr ); break;
            case 'group': $this->render_group( $field, $value, $name_attr ); break;
        }
        echo '</div>';
    }

    private function render_image_field($name_attr, $value) {
        $img_url = $value ? wp_get_attachment_url($value) : '';
        echo '<div class="sas-image-wrap"><input type="hidden" name="' . esc_attr($name_attr) . '" value="' . esc_attr( $value ) . '" class="sas-image-id">';
        if($img_url) echo '<img src="' . esc_url($img_url) . '" class="sas-image-preview">';
        echo '<button class="button sas-upload-image" style="margin-top:5px;">' . ($value ? 'Change Image' : 'Select Image') . '</button>';
        echo '<button class="button sas-remove-image" style="' . ($value ? '' : 'display:none') . '; margin-top:5px; margin-left:5px;">Remove</button></div>';
    }

    private function render_gallery_field($name_attr, $value) {
        echo '<div class="sas-gallery-container"><input type="hidden" name="' . esc_attr($name_attr) . '" value="' . esc_attr( $value ) . '" class="sas-gallery-ids"><div class="sas-gallery-wrapper">';
        if($value) {
            $ids = explode(',', $value);
            foreach($ids as $id) {
                if(empty($id)) continue;
                $url = wp_get_attachment_thumb_url($id) ?: wp_get_attachment_url($id);
                echo '<div class="sas-gallery-item" data-id="'.esc_attr($id).'"><img src="'.esc_url($url).'"><span class="sas-gallery-remove">×</span></div>';
            }
        }
        echo '</div><button class="button sas-add-gallery">Add to Gallery</button></div>';
    }

    private function render_repeater( $field, $value, $name_attr ) {
        $sub_fields = isset($field['sub_fields']) ? $field['sub_fields'] : [];
        if(empty($sub_fields)) { echo '<p>No sub fields defined.</p>'; return; }
        echo '<div class="sas-repeater-wrapper"><table class="sas-repeater-table"><thead><tr><th style="width:20px;"></th>';
        foreach($sub_fields as $sf) { echo '<th>' . esc_html($sf['label']) . '</th>'; }
        echo '<th style="width:30px;"></th></tr></thead><tbody class="sas-sortable">';
        if( is_array($value) && !empty($value) ) {
            foreach($value as $i => $row) { $this->render_repeater_row($i, $row, $sub_fields, $name_attr); }
        }
        echo '</tbody></table><div class="sas-actions"><button class="button button-primary sas-add-row">Add Row</button></div>';
        echo '<script type="text/html" class="sas-repeater-row-template">';
        $this->render_repeater_row('{{row_index}}', [], $sub_fields, $name_attr);
        echo '</script></div>';
    }

    private function render_repeater_row( $index, $row_data, $sub_fields, $parent_name ) {
        echo '<tr><td class="sas-handle"><span class="dashicons dashicons-menu"></span></td>';
        foreach($sub_fields as $sf) {
            echo '<td>';
            $field_name_html = $parent_name . '[' . $index . '][' . $sf['name'] . ']';
            $val = isset($row_data[$sf['name']]) ? $row_data[$sf['name']] : '';
            if($sf['type'] === 'image') $this->render_image_field($field_name_html, $val);
            elseif($sf['type'] === 'color') echo '<input type="text" name="' . esc_attr($field_name_html) . '" value="' . esc_attr( $val ) . '" class="sas-color-picker">';
            elseif($sf['type'] === 'textarea') echo '<textarea name="' . esc_attr($field_name_html) . '" class="sas-input" rows="2">' . esc_textarea($val) . '</textarea>';
            else echo '<input type="text" name="' . esc_attr($field_name_html) . '" value="' . esc_attr($val) . '" class="sas-input">';
            echo '</td>';
        }
        echo '<td><a href="#" class="sas-remove sas-remove-row"><span class="dashicons dashicons-trash"></span></a></td></tr>';
    }

    private function render_group( $field, $value, $name_attr ) {
        $sub_fields = isset($field['sub_fields']) ? $field['sub_fields'] : [];
        if(empty($sub_fields)) return;
        echo '<div class="sas-group-wrapper" style="border:1px solid #ddd; padding:15px; background:#fbfbfb;">';
        foreach($sub_fields as $sf) {
            $field_name_html = $name_attr . '[' . $sf['name'] . ']';
            $val = isset($value[$sf['name']]) ? $value[$sf['name']] : '';
            echo '<div class="sas-row"><label class="sas-label">' . esc_html( $sf['label'] ) . '</label>';
            if($sf['type'] === 'image') $this->render_image_field($field_name_html, $val);
            elseif($sf['type'] === 'color') echo '<input type="text" name="' . esc_attr($field_name_html) . '" value="' . esc_attr( $val ) . '" class="sas-color-picker">';
            elseif($sf['type'] === 'textarea') echo '<textarea name="' . esc_attr($field_name_html) . '" class="sas-input" rows="2">' . esc_textarea($val) . '</textarea>';
            else echo '<input type="text" name="' . esc_attr($field_name_html) . '" value="' . esc_attr($val) . '" class="sas-input">';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * 6. Save Data
     */
    public function save_post_meta( $post_id ) {
        if ( ! isset( $_POST['sas_data_nonce'] ) || ! wp_verify_nonce( $_POST['sas_data_nonce'], 'sas_save_data' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( isset( $_POST['sas_data'] ) ) {
            foreach ( $_POST['sas_data'] as $key => $value ) {
                if( is_array($value) && isset($value[0]) ) { $value = array_values($value); }
                update_post_meta( $post_id, $key, $value ); 
            }
        }
    }

    /**
     * 7. Options Page
     */
    public function add_options_page() {
        add_menu_page('Site Options', 'Site Options', 'manage_options', 'sas-options', [$this, 'render_options_page'], 'dashicons-admin-generic', 80);
    }

    public function render_options_page() {
        if( isset($_POST['sas_options_nonce']) && wp_verify_nonce($_POST['sas_options_nonce'], 'sas_save_options') ) {
            if ( isset( $_POST['sas_data'] ) ) {
                $options_data = get_option('sas_options_data', []);
                foreach ( $_POST['sas_data'] as $key => $value ) {
                    if( is_array($value) && isset($value[0]) ) $value = array_values($value);
                    $options_data[$key] = $value;
                }
                update_option('sas_options_data', $options_data);
                echo '<div class="notice notice-success is-dismissible"><p>Options Saved.</p></div>';
            }
        }
        $options_data = get_option('sas_options_data', []);
        echo '<div class="wrap"><h1>Site Options</h1>';
        
        $groups = get_posts(['post_type' => 'sas_field_group', 'posts_per_page' => -1]);
        $matching_groups = [];
        foreach($groups as $group) {
            $rules = get_post_meta( $group->ID, 'sas_location_rules', true );
            if(empty($rules) && get_post_meta($group->ID, 'sas_location', true) == 'options_page') { $matching_groups[] = $group; continue; }
            if($rules) { foreach($rules as $rule) { if($rule['param'] === 'options_page') { $matching_groups[] = $group; break; } } }
        }
        
        if(!empty($matching_groups)) {
            echo '<form method="post">';
            wp_nonce_field( 'sas_save_options', 'sas_options_nonce' );
            foreach($matching_groups as $group) {
                echo '<div class="postbox"><div class="postbox-header"><h2 class="hndle">'.esc_html($group->post_title).'</h2></div><div class="inside">';
                $fields = get_post_meta($group->ID, 'sas_fields', true);
                if($fields) {
                    foreach($fields as $field) {
                        $val = isset($options_data[$field['name']]) ? $options_data[$field['name']] : '';
                        $this->render_input($field, $val, 'sas_data');
                    }
                }
                echo '</div></div>';
            }
            echo '<input type="submit" class="button button-primary" value="Save Options"></form>';
        } else {
            echo '<div class="sas-empty-state"><h2>No Field Groups found.</h2><p>You haven\'t assigned any fields to this Options Page yet.</p><a href="' . admin_url('post-new.php?post_type=sas_field_group') . '" class="button button-primary button-large">Create New Field Group</a></div>';
        }
        echo '</div>';
    }
}

new SAS_Custom_Fields();

if ( ! function_exists( 'get_sas_field' ) ) {
    function get_sas_field( $key, $post_id = null ) {
        if ( ! $post_id ) $post_id = get_the_ID();
        $val = get_post_meta( $post_id, $key, true );
        if( is_string($val) && strpos($val, ',') !== false && is_numeric(substr($val, 0, 1)) ) {
            $val = explode(',', $val);
        }
        if ( $val === '' || $val === false ) {
            $options = get_option('sas_options_data', []);
            if( isset($options[$key]) ) {
                $val = $options[$key];
            }
        }
        return $val;
    }
}