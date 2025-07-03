<?php
/**
 * Enqueue all necessary scripts and styles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Esab_Enqueue' ) ) {

    class Esab_Enqueue {

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
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets'], 2);
        }

        /**
         * Editor assets enqueue
         */
        public function enqueue_editor_assets(){

            $dep = ESAB_PATH . 'build/modules/index.asset.php';
            if( file_exists( $dep ) ) {
                $asset = require_once $dep;

                wp_enqueue_script(
                    'esab-modules',
                    ESAB_URL . 'build/modules/index.js',
                    $asset['dependencies'],
                    $asset['version'],
                    false
                );

                wp_enqueue_style(
                    'esab-modules',
                    ESAB_URL . 'build/modules/style-index.css',
                    [],
                    ESAB_VERSION
                );

                wp_localize_script( 'esab-modules', 'esabData', [
                    'hasPro' => class_exists( 'Esabp_Accordion_Block_Pro' ) ? true : false
                ] );

            }

            $gdep = ESAB_PATH . 'build/global/index.asset.php';
            if( file_exists( $gdep ) ) {
                $gasset = require_once $gdep;

                wp_enqueue_script(
                    'esab-editor-global',
                    ESAB_URL . 'build/global/index.js',
                    $gasset['dependencies'],
                    $gasset['version'],
                    false
                );

                wp_enqueue_style(
                    'esab-editor-global',
                    ESAB_URL . 'build/global/style-index.css',
                    [],
                    ESAB_VERSION
                );
            }

        }
    }

    new Esab_Enqueue();
}