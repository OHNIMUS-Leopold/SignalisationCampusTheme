<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<html>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script type="importmap">
      {
        "imports": {
          "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
          "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
        }
      }
    </script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  </head>
  <body>
    <div class="wrap">
      <header class="header">
        <button class="bouton" id="toggleMenu">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/menu.svg" alt="Menu">
        </button>
        <?php wp_nav_menu(
          array(
            'theme_location' => 'main',
            'container' => 'nav',
            'container_class' => 'main-nav'
          ));
        ?>
        <p class="titre"><a href="<?php echo home_url(); ?>" style="text-decoration: none; color: #ffffff;"><?php bloginfo('name'); ?></a></p>
        <button class="bouton" id="toggleScan">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/scan.svg" alt="Scanner">
        </button>
      </header>

      





<script>

document.addEventListener("DOMContentLoaded", function() {
  var menuButton = document.getElementById('toggleMenu');
  var mainNav = document.querySelector('.main-nav');

  menuButton.addEventListener('click', function() {
    if (mainNav.style.display === 'block') {
      mainNav.style.display = 'none';
    } else {
      mainNav.style.display = 'block';
    }
  });
});

document.addEventListener("DOMContentLoaded", function() {
  var menuButton = document.getElementById('toggleScan');
  var mainNav = document.querySelector('.QRCode');

  menuButton.addEventListener('click', function() {
    if (mainNav.style.display === 'block') {
      mainNav.style.display = 'none';
    } else {
      mainNav.style.display = 'block';
    }
  });
});

</script>