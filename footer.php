      <footer>
        <?php bloginfo('name'); ?> est propulsé par <a href="https://wordpress.org">WordPress</a>.
        <?php wp_nav_menu(
            array(
              'theme_location' => 'footer',
              'container' => 'nav',
              'container_class' => 'footer-nav'
            ));
           ?>
      </footer>
    </div>
    <?php wp_footer(); ?>
  </body>
</html>