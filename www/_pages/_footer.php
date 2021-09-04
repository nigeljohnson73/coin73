<!--
  _____           _
 |  ___|__   ___ | |_ ___ _ __
 | |_ / _ \ / _ \| __/ _ \ '__|
 |  _| (_) | (_) | ||  __/ |
 |_|  \___/ \___/ \__\___|_|

-->
<footer class="text-center" data-ng-controller="FooterCtrl">
	<nav class="navbar navbar-expand nav-fill navbar-light bg-light">
<!-- 		<ul class="navbar-nav mr-auto"> -->
		<ul class="navbar-nav w-100 nav-justified">
			<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
			<li class="nav-item"><a class="nav-link" href="/signup">Sign up</a></li>
			<li class="nav-item"><a class="nav-link" href="/merch">Merch</a></li>
			<li class="nav-item"><a class="nav-link" href="/about">About</a></li>
		</ul>
	</nav>
	<div>
		<p class="float-left">
			&nbsp;&nbsp;<a href="/terms">Terms of service</a>&nbsp;&nbsp;
		</p>


		<p class="float-right">
			&nbsp;&nbsp;<a href="/privacy">Privacy policy</a>&nbsp;&nbsp;
		</p>


		<p class="d-block d-sm-none">
			&copy; 2020 - {{nowDate | date : 'yyyy'}} Nigel Johnson<br />all rights reserved
		</p>
		<p class="d-none d-sm-block">&copy; 2020 - {{nowDate | date : 'yyyy'}} Nigel Johnson, all rights reserved.</p>

		<!--	<div style="position: fixed; bottom: 10px; left: 20px; font-size: 5pt; color: #ccc;">
 			<span class="glyphicon glyphicon-signal" data-ng-hide="!online"></span> <span class="glyphicon glyphicon-plane" data-ng-hide="online"></span>
 		</div> 
 -->

		<div style="position: fixed; bottom: 10px; right: 20px; font-size: 5pt; color: #ccc;">
			<span class="size-indicator d-block d-sm-none">XS</span> <span class="size-indicator d-none d-sm-block d-md-none">SM</span> <span class="size-indicator d-none d-md-block d-lg-none">MD</span> <span class="size-indicator d-none d-lg-block d-xl-none">LG</span> <span class="size-indicator d-none d-xl-block">XL</span>
		</div>
	</div>
</footer>
<script>
$(document).ready(function() {
	// Hide protected images
	$('.covered').each(function() {

		$(this).append('<cover></cover>');
		$(this).mousedown(function(e) {
			if (e.button == 2) {
				e.preventDefault();
				return false;
			}
			return true;
		});

		$('img', this).css('display', 'block');
		$(this).hover(function() {
			var el = $('cover', this);
			if (el.length <= 0) {
				$(this).html('');
			}
		});
	});


	// get current URL path and assign 'active' class
	var pathname = window.location.pathname;
	$('.navbar-nav > li > a[href="'+pathname+'"]').parent().addClass('active');
});

</script>
</body>
</html>