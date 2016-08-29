<nav class="navbar">
  <div class="container inov-nav">
    <?php if(user_is_logged_in()): ?><!-- start of logged user -->
    <ul id="logged-nav">
      <li><a href="/web-app/">Take Test</a></li>
      <li><a href="/content/file-downloads">Print Tests</a></li>
      <li><a href="/user">Account</a></li>
      <li><a href="/user/logout">Logout</a></li>
    </ul>
    <?php endif; ?> <!-- end of logged user -->
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <div class="burger-menu"><i class="fa fa-bars fa-2x" aria-hidden="true"></i></div>
      </button>
      <a class="navbar-brand" id="navbar-brand" href="/">
        <img src="/sites/all/themes/custom/test_innovators/images/logo-ssat.png">
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="main-nav">
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
          <li>1-800-280-1857</li>
          <li><a href="/testimonials">Results</a></li>
          <li class="dropdown"><a href="/content/store" class="dropdown-toggle disabled" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Pricing<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="/content/elementary-ssat-grades-3-4">Elementary Level</a></li>
              <li><a href="/content/middle-level-ssat">Middle Level</a></li>
              <li><a href="/content/upper-level-ssat">Upper Level</a></li>
            </ul>
          </li>
          <li><a href="/content/schools">School Data</a></li>
          <li><a href="/content/benefits">How It Works</a></li>
          <?php if(!user_is_logged_in()): ?>
          <li><a href="/user">Login</a></li>
          <?php endif; ?> 
          <li><a href="/cart"><i class="fa fa-shopping-cart"></i></a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div>
  </div><!-- /.container-fluid -->
</nav>