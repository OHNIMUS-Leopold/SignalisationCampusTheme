<?php get_header(); ?>

<div>
    <?php
    
    // On récupère l'ID du post actuel
    $post_id = get_the_ID();
    
    // On récupère les valeurs des champs personnalisés
    $nom = get_post_meta($post_id, 'nom', true);
    $site = get_post_meta($post_id, 'site', true);
    $telephone = get_post_meta($post_id, 'telephone', true);
    $mail = get_post_meta($post_id, 'mail', true);
    $adresse = get_post_meta($post_id, 'adresse', true);
    $ville = get_post_meta($post_id, 'ville', true);
    $code_postal = get_post_meta($post_id, 'code_postal', true);
    $responsable_nom = get_post_meta($post_id, 'responsable_nom', true);
    $responsable_mail = get_post_meta($post_id, 'responsable_mail', true);
    $responsable_telephone = get_post_meta($post_id, 'responsable_telephone', true);
    $coordonnees = get_post_meta($post_id, 'coordonnees', true);

    // Idem pour l'image à la une
    $image = get_the_post_thumbnail_url($post_id, 'large');
    ?>
    
    
    
    <div>
    <?php
        // Afficher les valeurs des champs personnalisés
        echo '<img class="image-thumb" src="' . $image . '" alt="Image de ' . $nom . '">';
    ?>
    </div>
    <div class="arrondi"></div>
    <div class="details">    
    <?php
        echo '<p class="details-title">' . $nom . '</p>';
        ?>
        <div class="details-div details-loc">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/pin.svg" alt="location">
            <?php
            echo '<p class="details-div-info">' . $adresse . ', ' . $code_postal . ' ' . $ville . '</p>';
            ?>
        </div>

        <div class="details-div details-phone">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/phone.svg" alt="téléphone">
            <?php
            echo '<p class="details-div-info">' . $telephone . '</p>';
            ?>
        </div>

        <div class="details-div details-mail">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/mail.svg" alt="adresse mail">
            <?php
            echo '<p class="details-div-info"><a href="mailto:' . esc_attr($mail) . '">' . $mail . '</a></p>';
            ?>
        </div>

        <div class="details-div details-site">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/globe.svg" alt="site web">
            <?php
            echo '<p class="details-div-info"><a href="' . esc_url($site) . '">' . $site . '</a></p>';
            ?>
        </div>

        <div class="itineraire">
            <p class="title-resp">Créer un itinéraire</p>
            <div class="itineraire-img">
                <?php
                    // Afficher le lien Google Maps avec les coordonnées GPS
                    echo '<a id="googleMapsLink" href="#">' .
                    '<img src="' . get_template_directory_uri() . '/assets/apercuMaps.png" alt="créer un itinéraire">' .
                    '</a>';
                ?>
            </div>
        </div>
        

        <?php
        if ($responsable_nom !== '' ) {
            ?>
            <p class="title-resp">Responsable</p>
            <?php
            echo '<p class="details-div-resp">' . 'Nom : ' . $responsable_nom . '</p>';
            echo '<p class="details-div-resp">' . 'Téléphone : ' . $responsable_telephone . '</p>';
            echo '<p class="details-div-resp"><a href="mailto:' . esc_attr($responsable_mail) . '">' . 'Mail : ' . $responsable_mail . '</a></p>';
        }
        else {
            
        }
        


        
    ?>
    </div>
    
    
    
</div>



<script>
    // Déclaration des variables à l'extérieur de la fonction pour les rendre accessibles globalement
    let startLatitude = "START_LATITUDE";
    let startLongitude = "START_LONGITUDE";
    let destinationCoordinates = <?php echo json_encode($coordonnees); ?>;

    // Fonction pour mettre à jour la position de l'utilisateur
    function updateUserPosition(position) {
        // Appel des coordonnées GPS de l'utilisateur
        startLatitude = position.coords.latitude;
        startLongitude = position.coords.longitude;

        // On met à jour le lien Google Maps avec les nouvelles coordonnées de l'utilisateur
        const googleMapsLink = "https://www.google.com/maps/dir/" + startLatitude + "," + startLongitude + "/" + destinationCoordinates;
        document.getElementById('googleMapsLink').href = googleMapsLink;
    }

    // Appel de la fonction au chargement de la page pour obtenir les coordonnées initiales
    document.addEventListener('DOMContentLoaded', function() {
        navigator.geolocation.getCurrentPosition(updateUserPosition);
    });
</script>
