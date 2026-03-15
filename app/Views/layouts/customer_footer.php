<?php
/**
 * Layout footer cho khu vực Khách hàng
 */
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
<footer class="site-footer">
	<div class="footer-top">
		<div class="container">
			<div class="footer-columns">
				<div class="footer-col connect">
					<h3>Connect</h3>
					<p><i class="fa-solid fa-location-dot"></i> The Caffe</p>
					<div class="social">
						<a href="#" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
						<a href="#" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
					</div>
				</div>
				<div class="footer-col links">
					<h3>Links</h3>
					<ul>
						<li><a href="<?php echo url('customer_home'); ?>">Trang chủ</a></li>
						<li><a href="<?php echo url('customer_menu'); ?>">Sản phẩm</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="footer-bottom">
		<div class="container footer-bottom-inner">
			<div class="copyright">Copyright © <?php echo date('Y'); ?> - The Caffe</div>
		</div>
	</div>
</footer>
<style>
.site-footer{background:#080808;color:#e9e9e9;padding:40px 0 0}.site-footer .container{max-width:1100px;margin:0 auto;padding:0 20px}
.footer-columns{display:flex;gap:40px;align-items:flex-start}.footer-col{flex:1}
.site-footer h3{font-size:20px;margin-bottom:12px;color:#fff}.footer-col p{margin:0 0 12px;color:#d4d4d4}
.social a{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:#eef0f3;color:#111;border-radius:6px;margin-right:10px;text-decoration:none}
.footer-col ul{list-style:none;padding:0;margin:0}.footer-col ul li{margin-bottom:8px}.footer-col ul li a{color:#bbb;text-decoration:none}
.footer-bottom{background:#3a383c;padding:12px 0;margin-top:20px}.footer-bottom-inner{display:flex;justify-content:center;align-items:center}
.copyright{color:#e6e6e6;font-size:13px;text-align:center}
@media (max-width:800px){.footer-columns{flex-direction:column}}
</style>
