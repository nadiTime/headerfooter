<nav class="navbar">
  <div class="container inov-nav">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="true">
        <span class="sr-only">Toggle navigation</span>
        <div class="burger-menu"><i class="fa fa-bars fa-2x" aria-hidden="true"></i></div>
      </button>
      <a class="navbar-brand" id="navbar-brand" href="/">
        <img src="/sites/all/themes/custom/test_innovators/images/logo-test.png">
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="main-nav">
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
          <li style="padding: 0;"></li>
          <li><a href="/results">Results</a></li>
          <li><a href="/how-it-works">How It Works</a></li>
          <li><a href="/schools">For Schools</a></li>
          <li><a href="/tutors">For Tutors</a></li>
           <li class="dropdown"><a id="for-students-link" href="#" class="dropdown-toggle disabled" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">For Students<span class="caret expends-OM"></span></a>
            <ul class="dropdown-menu">
              <li><a href="//iseepracticetest.com">ISEE</a></li>
              <li><a href="//ssatpracticetest.com">SSAT</a></li>
            </ul>
          </li> 
        </ul>
      </div><!-- /.navbar-collapse -->
    </div>
  </div><!-- /.container-fluid -->
</nav>
<script type="text/javascript">
function navbar_dropdown_mod(){
  $(".dropdown-toggle").toggleClass("disabled");
  $("#for-students-link").click(function(){
    $(".navbar-collapse").toggleClass("expand");
  });
}
var width = $(window).width();
if(width<768){
 navbar_dropdown_mod();
}
$( window ).resize(function() {
  if(width<768){
    navbar_dropdown_mod();
  }
});

</script>