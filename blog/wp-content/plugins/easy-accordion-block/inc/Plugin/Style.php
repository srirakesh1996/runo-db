<?php
/**
 * Initialize all necessary classes and functions
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Esab_Style' ) ) {

    class Esab_Style {

        /**
         * Instance of the class
         *
         * @var null
         */
        private static $instance = null;

        /**
         * Constructor
         */
        public function __construct() {
            add_filter( 'render_block', [ $this, 'generate_style' ], 10, 2 );
            // editor css
            add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ] );
        }

        /**
         * Generate Style
         * 
         * @return void
         */
        public function generate_style( $block_content, $block ) {

            if ( isset( $block['blockName'] ) && str_contains( $block['blockName'], 'esab/' ) ) {
                
                // Trigger the custom action for rendering the block.
                do_action( 'esab_render_block', $block );
            
                $attrs = $block['attrs'] ?? [];
            
                // Early return if attributes are empty.
                if ( empty( $attrs ) ) {
                    return $block_content;
                }
            
                // Get the unique ID safely.
                $unique_id = $attrs['uniqueId'] ?? '';
                if ( empty( $unique_id ) ) {
                    return $block_content;
                }
            
                $block_style = $attrs['blockStyle'] ?? '';

                if ( ! empty( $block_style ) ) {
                    $handle = 'esab-accordion-style-' . $unique_id;
                    $this->render_inline_css( $handle, $block_style );
                    return $block_content;
                }
            
                // Define default attributes.
                $defaults = [
                    'accordionsGap'              => 10,
                    'marginTop'                  => '0',
                    'marginBottom'               => '0',
                    'marginLeft'                 => '0',
                    'marginRight'                => '0',
                    'accordionBorderWidth'       => 1,
                    'accordionBorderStyle'       => 'solid',
                    'accordionBorderColor'       => '#E0E0E0',
                    'accordionActiveBorderColor' => '#3fa796',
                    'accordionBorderRadius'      => '3',
                    'headingActiveColor'         => '#3fa796',
                    'headerActiveBg'             => '#f0fffd',
                    'headerTopPadding'           => '10',
                    'headerBottomPadding'        => '10',
                    'headerLeftPadding'          => '10',
                    'headerRightPadding'         => '10',
                    'linkedHeaderPadding'        => '10',
                    'enableLinkedHeaderPadding'  => false,
                    'iconSize'                   => 20,
                    'activeIconColor'            => '#3fa796',
                    'bodyActiveBg'               => '#f0fffd',
                    'bodyTopPadding'             => '10',
                    'bodyBottomPadding'          => '10',
                    'bodyLeftPadding'            => '10',
                    'bodyRightPadding'           => '10',
                    'linkedBodyPadding'          => '10',
                    'enableLinkedBodyPadding'    => false,
                    'inactiveIconColor'          => 'inherit',  // Added to ensure it's always defined.
                    'zindex'                     => null,       // Added to prevent undefined errors.
                ];
            
                // Merge attributes with defaults.
                $attributes = wp_parse_args( $attrs, $defaults );
            
                // Start building custom CSS.
                $custom_css = '';
                $prefix = '.' . $unique_id;
            
                // Handle z-index if set.
                if ( isset( $attributes['zindex'] ) ) {
                    $custom_css .= "{$prefix} { z-index: {$attributes['zindex']}; }";
                }
            
                // Accordion styles.
                if ( isset( $attributes['accordionBorderRadius'] ) ) {
                    $custom_css .= "{$prefix} .wp-block-esab-accordion-child { border-radius: {$attributes['accordionBorderRadius']}px; }";
                }
                if ( isset( $attributes['accordionActiveBorderColor'] ) ) {
                    $custom_css .= "{$prefix} .wp-block-esab-accordion-child.esab__active_accordion { border-color: {$attributes['accordionActiveBorderColor']} !important; }";
                }
                if ( isset( $attributes['headerActiveBg'] ) ) {
                    $custom_css .= "{$prefix} .wp-block-esab-accordion-child.esab__active_accordion .esab__head { background: {$attributes['headerActiveBg']} !important; }";
                }
                if ( isset( $attributes['headingActiveColor'] ) ) {
                    $custom_css .= "{$prefix} .wp-block-esab-accordion-child.esab__active_accordion .esab__heading_tag { color: {$attributes['headingActiveColor']} !important; }";
                }
                if ( isset( $attributes['bodyActiveBg'] ) ) {
                    $custom_css .= "{$prefix} .wp-block-esab-accordion-child.esab__active_accordion .esab__body { background-color: {$attributes['bodyActiveBg']} !important; }";
                }
            
                // Icon styles.
                $icon_size = isset( $attributes['iconSize'] ) ? $attributes['iconSize'] : 20;
                $inactive_icon_color = isset( $attributes['inactiveIconColor'] ) ? $attributes['inactiveIconColor'] : 'inherit';
                $active_icon_color = isset( $attributes['activeIconColor'] ) ? $attributes['activeIconColor'] : 'inherit';
            
                $custom_css .= "{$prefix} .esab__collapse svg { width: {$icon_size}px; fill: {$inactive_icon_color}; }";
                $custom_css .= "{$prefix} .esab__expand svg { width: {$icon_size}px; fill: {$active_icon_color}; }";
            
                // Render the inline CSS.
                $handle = 'esab-accordion-style-' . $unique_id;
                $this->render_inline_css( $handle, $custom_css );
            }
        
            return $block_content;
        }
        /**
         * Render Inline CSS
        */
        public function render_inline_css( $handle, $css ) {
            wp_register_style( $handle, false, array(), ESAB_VERSION, 'all' );
            wp_enqueue_style( $handle, false, array(), ESAB_VERSION, 'all' );
            wp_add_inline_style( $handle, $css );
        }

        /**
         * Editor Assets
         * 
         * @return void
         */
        public function editor_assets() {
            wp_enqueue_style( 'esab-accordion-editor', ESAB_URL . 'css/editor.css', [], ESAB_VERSION );
        }

        /**
         * Instance of the class
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

    Esab_Style::instance(); // Initialize the class

}