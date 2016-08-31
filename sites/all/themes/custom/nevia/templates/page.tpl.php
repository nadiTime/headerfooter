<?php
  $url_path = parse_url($GLOBALS['base_url']); 
  $path = $url_path['host'];
?>

<!-- Wrapper / Start -->
<link href="/sites/all/themes/custom/test_innovators/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/sites/all/themes/custom/test_innovators/css/font-awesome-4.6.3/css/font-awesome.min.css">
<link rel="stylesheet" href="/sites/all/themes/custom/test_innovators/css/style1.css">
 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

    <!-- Include all compiled plugins (below), or include individual files as needed -->

<script type="text/javascript">
  jQuery.browser = {};
  (function () {
      jQuery.browser.msie = false;
      jQuery.browser.version = 0;
      if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
          jQuery.browser.msie = true;
          jQuery.browser.version = RegExp.$1;
      }
  })();
</script>
<div id="wrapper">

  <!-- header
  ================================================== -->
  <?php
  ?>
  <!-- Content
  ================================================== -->
  <div id="content">
    <?php if ($page['logged_user_menu']): ?>
      <div class="row" id="logged-user-menu">
        <?php
        ?>
      </div>
  <?php endif; ?>
    <?php if ($page['slider']): ?>
      <section id="layerslider-container">
        <?php print render($page['slider']); ?>
      </section>
    <?php endif; ?>

    <?php if ($page['highlighted']): ?>
      <div class="container">
        <div class="sixteen columns">
          <div id="highlighted">
            <?php print render($page['highlighted']); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
      <?php if ($title): ?>
        <?php if (!drupal_is_front_page()): ?> <!-- NOT FRONT PAGE -->
            <div class="row" id="header-not-front-page">
              <?php print $header ?>
              <div class="col-xs-12">
                <hr>
              </div>
            </div>
          <!-- 960 Container -->
          <div class="container">
            <div class="sixteen page-title">

              <h2><?php print $title; ?></h2>


              <?php if ($breadcrumb): ?>
                <nav id="breadcrumbs">

                  <?php print $breadcrumb; ?>
                </nav>
              <?php endif; ?>

            </div>
          </div>
          <!-- 960 Container / End -->
        <?php endif; ?>
      <?php endif; ?>

      <?php if (drupal_is_front_page()): ?> <!-- FRONT PAGE -->
        <script type="text/javascript">
            $(function() {
              $('a[href*=#]:not([href=#])').click(function() {
              if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'')
                  || location.hostname == this.hostname) {

                  var target = $(this.hash);
                  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                     if (target.length) {
                       $('html,body').animate({
                           scrollTop: target.offset().top
                      }, 1000);
                      return false;
                    }
                 }
              });
            });
          </script>
        <div class="row" id="header">
           <?php print $header ?>
          </div>
      <?php endif; ?>
    <?php if (drupal_is_front_page() && !theme_get_setting('homepage_title', 'nevia')): ?>
      <div class="container floated">
        <div class="sixteen floated page-title">

          <h2><?php print $title; ?></h2>


          <?php if ($breadcrumb): ?>
            <nav id="breadcrumbs">

              <?php print $breadcrumb; ?>
            </nav>
          <?php endif; ?>

        </div>
      </div>
    <?php endif; ?>



    <!-- 960 Container -->
    <div class="container <?php print $containner_class; ?>">


      <!-- Page Content -->
      <div class="<?php print $content_class; ?>">
        <section class="page-content">
          <?php print $messages; ?>


          <a id="main-content"></a>
          <?php print render($title_prefix); ?>
          <?php print render($title_suffix); ?>
          <?php if ($tabs): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
          <?php print render($page['help']); ?>
          <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
          <?php print render($page['content']); ?>
          <?php print $feed_icons; ?>
        </section>
      </div>
      <!-- Page Content / End -->

      <?php if ($page['sidebar_first']): ?>
        <div id="sidebar-first" class="four floated sidebar left">
          <aside class="sidebar">
            <div class="section">
              <?php print render($page['sidebar_first']); ?>
            </div>
          </aside>
        </div>
      <?php endif; ?>

      <?php if ($page['sidebar_second']): ?>
        <div id="sidebar-second" class="four floated sidebar right">
          <aside class="sidebar">
            <div class="section">
              <?php print render($page['sidebar_second']); ?>
            </div>
          </aside>
        </div>
      <?php endif; ?>

      <div class="clearfix"></div>

    </div>
    <!-- 960 Container / End -->



    <?php if ($page['home_recent_work']): ?>
      <div class="container floated">
        <div class="blank floated">
          <?php print render($page['home_recent_work']); ?>
        </div>
      </div>
    <?php endif; ?>


    <?php if ($page['home_recent_news'] || $page['home_testimonial']): ?>
      <div class="container">
        <?php if ($page['home_recent_news']): ?>
          <div class="eight columns">
            <?php print render($page['home_recent_news']); ?>
          </div>
        <?php endif; ?>

        <?php if ($page['home_testimonial']): ?>
          <div class="eight columns">
            <?php print render($page['home_testimonial']); ?>
          </div>
        <?php endif; ?>

      </div>
    <?php endif; ?>

  </div>
  <!-- Content / End -->
  <div id="footer">
    <?php include_once 'headers_footers/footer_'.$path.'.php'; ?>
    <div class="container">
      <div class="sixteen columns">
        <?php print render($page['footer']); ?>
      </div>
    </div>
  </div>
</div>
<!-- Wrapper / End -->


<!-- Footer
================================================== -->
