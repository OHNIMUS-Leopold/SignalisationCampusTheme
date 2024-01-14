<?php
/*
Template Name: Page livraison
*/
?>

<?php get_header(); ?>

<div class="livraison">
    <h1 class="livraison-title">Appeler le Pays de Montbéliard Agglomération</h1>
    <img class="livraison-img" src="<?php echo get_template_directory_uri(); ?>/assets/logoPMA.png" alt="Logo Pays de Montbéliard Agglomération">
    <button class="livraison-btn" onclick="appelerNumero('+33 (3) 81 31 88 88')">Appeler</button>
</div>

<script>
    function appelerNumero(numero) {
        // On vérifie si la fonction "navigator" est disponible dans le navigateur
        if (navigator && navigator.msLaunchUri) {
            navigator.msLaunchUri('tel:' + numero, null);
        } else {
            // Sinon, on  utilise la méthode window.location.href pour déclencher l'appel
            window.location.href = 'tel:' + numero;
        }
    }
</script>