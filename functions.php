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




    // Fonction de recherche utilisée avec Ajax

    add_action('wp_ajax_search_departements', 'search_departements_callback');
    add_action('wp_ajax_nopriv_search_departements', 'search_departements_callback');

    function search_departements_callback() {
        // Récupération du terme de recherche
        $search_term = sanitize_text_field($_GET['search_term']);

        // Récupération des résultats de recherche
        $args = array(
            // 'post_type' => array('departement', 'formation'),
            'post_type' => 'departement',
            's' => $search_term,
            'orderby' => 'title',
            //'orderby' => 'post_type', // Tri par type de poste
            'order' => 'ASC', // Tri ascendant pour mettre 'departement' en premier
        );

        $query = new WP_Query($args);

        // Boucle pour afficher les résultats
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                echo '<p class="list-item"><a class="list-item-link" href="' . get_permalink() . '">' . get_the_title() . '</a></p>';
                echo '<hr class="list-item-hr">';
            endwhile;
        else :
            echo '<p class="list-item list-item-link" style="text-align: center;">Aucun résultat trouvé.</p>';
        endif;

        // Réinitialisation des requêtes WordPress
        wp_reset_postdata();
        wp_die();
    }


    // Affichage des départements en fonction du numéro

    add_action('wp_ajax_filtrer_contenu_par_numero', 'filtrer_contenu_par_numero_callback');
    add_action('wp_ajax_nopriv_filtrer_contenu_par_numero', 'filtrer_contenu_par_numero_callback');

    function filtrer_contenu_par_numero_callback() {
        // Récupération du numéro depuis la requête AJAX
        $numero = sanitize_text_field($_POST['numero']);

        $args = array(
            'post_type' => 'departement',
            'meta_query' => array(
                array(
                    'key' => 'numero', 
                    'value' => $numero,
                    'compare' => '='
                )
            )
        );

        $query = new WP_Query($args);

        // Boucle pour afficher les résultats
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $post_title = get_the_title();
                // $post_content = get_the_content();
                echo '<h1 class="preview-title">' . $post_title . '</h1>';
                // Bouton vers la page du département
                echo '<a class="preview-link" href="' . get_permalink() . '">Voir plus ></a>';
            endwhile;
        else :
            echo '<p class="list-item list-item-link" style="text-align: center;">Aucune donnée à afficher.</p>';
        endif;

        // Réinitialisation des requêtes WordPress
        wp_reset_postdata();
        wp_die();
    }



    
    // Cas de MP

    add_action('wp_ajax_filtrer_contenu_par_numeroMP', 'filtrer_contenu_par_numero_callbackMP');
    add_action('wp_ajax_nopriv_filtrer_contenu_par_numeroMP', 'filtrer_contenu_par_numero_callbackMP');
    
    function filtrer_contenu_par_numero_callbackMP() {
        // Récupération du numéro depuis la requête AJAX
        $numero = sanitize_text_field($_POST['numero']);
    
        $args = array(
            'post_type' => 'departement',
            'meta_query' => array(
                array(
                    'key' => 'numero', 
                    'value' => $numero,
                    'compare' => '='
                )
            )
        );
    
        $query = new WP_Query($args);
    
        // Boucle pour afficher les résultats
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $post_title = get_the_title();
                // $post_content = get_the_content();
                echo '<h1 class="preview-title">' . $post_title . '</h1>';
                // Bouton vers la page du département
                echo '<a class="preview-link" href="' . get_permalink() . '">Voir plus ></a>';
            endwhile;
        else :
            echo '<p class="list-item list-item-link" style="text-align: center;">Aucune donnée à afficher.</p>';
        endif;
    
        // Réinitialisation des requêtes WordPress
        wp_reset_postdata();
        wp_die();
    }



?>