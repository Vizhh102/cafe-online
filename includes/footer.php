<?php
// Footer include - outputs site's footer markup
?>
<!-- Font Awesome for social icons (safe to remove if loaded globally) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pbVd5Q6Y6qYb6t1KQxvYkqG0Vg+Q1Qv2lY7p0gJZ3x6b7kqK9Q9hW6m4Y3e3l1Q2s6v1Z2X3Y4Z5p6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<footer class="site-footer">
	<div class="footer-top">
		<div class="container">
			<div class="footer-columns">
				<div class="footer-col connect">
					<h3>Connect</h3>
					<p><i class="fa-solid fa-location-dot"></i> Sienna Towers, Service Road, Bangalore</p>
					<div class="social">
						<a href="#" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
						<a href="#" title="Twitter"><i class="fa-brands fa-twitter"></i></a>
						<a href="#" title="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
						<a href="#" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
					</div>
				</div>

				<div class="footer-col links">
					<h3>Links</h3>
					<ul>
						<li><a href="/index.php">Home</a></li>
						<li><a href="/customer/index.php">Team</a></li>
						<li><a href="#">Blogs</a></li>
						<li><a href="#">Support</a></li>
					</ul>
				</div>

				<div class="footer-col newsletter">
					<h3>Newsletter</h3>
					<form class="newsletter-form" action="#" method="post">
						<input type="email" name="email" placeholder="Your email id here" required>
						<button type="submit">Subscribe</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="footer-bottom">
		<div class="container footer-bottom-inner">
			<!-- footer navigation removed per request -->
			<div class="copyright">Copyright © <?php echo date('Y'); ?></div>
		</div>
	</div>
</footer>

<!-- Scoped footer styles: minimal, overrideable by main stylesheet -->
<style>
	.site-footer{background:#080808;color:#e9e9e9;font-family:Georgia, 'Times New Roman', serif;padding:40px 0 0}
	.site-footer .container{max-width:1100px;margin:0 auto;padding:0 20px}
	.footer-columns{display:flex;gap:40px;align-items:flex-start}
	.footer-col{flex:1}
	.site-footer h3{font-size:20px;margin-bottom:12px;color:#fff}
	.footer-col p{margin:0 0 12px;color:#d4d4d4}
	.social a{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:#eef0f3;color:#111;border-radius:6px;margin-right:10px;text-decoration:none}
	.social a i{font-size:16px}
	.footer-col ul{list-style:none;padding:0;margin:0}
	.footer-col ul li{margin-bottom:8px}
	.footer-col ul li a{color:#bbb;text-decoration:none}
	.newsletter-form input{width:100%;padding:12px;border-radius:4px;border:0;margin-bottom:10px}
	.newsletter-form button{width:100%;padding:12px;background:#c79a1a;color:#111;border:0;border-radius:4px;cursor:pointer}
	.footer-bottom{background:#3a383c;padding:12px 0;margin-top:20px}
	.footer-bottom-inner{display:flex;justify-content:center;align-items:center}
	.footer-nav a{color:#dfe0e2;margin-right:14px;text-decoration:none;font-size:13px}
	.copyright{color:#e6e6e6;font-size:13px;display:block;width:100%;margin:0 auto;text-align:center !important}

	/* Responsive */
	@media (max-width:800px){
		.footer-columns{flex-direction:column}
		.footer-bottom-inner{flex-direction:column;gap:8px}
		.footer-nav{display:flex;flex-wrap:wrap;justify-content:center}
	}
</style>

