<?php
    function signalisationtheme_setup() {
        // Ajout du support des images mises en avant
        add_theme_support('post-thumbnails');
        // Ajout du support des titres
        add_theme_support('title-tag');
        // Ajout du support pour rendre le code valide en html5
        add_theme_support(
            'html5', 
            array(
                'comment-list', 
                'comment-form', 
                'search-form',
                'gallery',
                'caption',
                'style',
                'script'
            )
        );
        // Ajout du support pour les menus
        register_nav_menus(
            array(
                'main' => 'Menu Principal',
                'footer' => 'Bas de page'
            )
        );
    }
    add_action('after_setup_theme', 'signalisationtheme_setup');

    
    // Ajout des styles et des scripts
    function signalisationtheme_script() {
        // Ajout du fichier style.css à la racine du thème
        wp_enqueue_style('style', get_stylesheet_uri());
        // Ajout du fichier main.js
        wp_enqueue_script('main', get_template_directory_uri() . '/js/script.js', array(), '1.0', true);
    }

    add_action('wp_enqueue_scripts', 'signalisationtheme_script');


    // Nouveaux mime types autorisés
    function my_custom_mime_types( $mimes ) {
        // gltf
        $mimes['gltf'] = 'model/gltf+json';
        // glb
        $mimes['glb'] = 'model/gltf-binary';
        // bin
        $mimes['bin'] = 'application/octet-stream';
        return $mimes;
    }
    add_filter( 'upload_mimes', 'my_custom_mime_types' );

?>