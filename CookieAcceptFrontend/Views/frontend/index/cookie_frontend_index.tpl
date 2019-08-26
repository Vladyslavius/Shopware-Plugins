{namespace name="frontend/cookie_frontend/index/cookie_frontend_index"}
{block name='frontend_index_header_javascript_jquery' append}
	{if $hide_cookie_code_box eq 0}
		{literal}
		<script type="text/javascript">
			$(document).ready(function() {
				showBox();
				jQuery(".cookie-accept-button button").on("click", function(event) {
					setCookie();
				});
			});
			function showBox() {
				$("#logout-bg").removeClass("slide-down").animate({bottom: "30px"}, 1000);
			}
			function hideBox() {
				$("#logout-bg").animate({bottom: "-400px"}, 1000);
			}
			function setCookie() {
				var url = "{/literal}{url controller='CookieAcceptFrontendController' action='setCookie'}{literal}";
				$.ajax({
					url: url,
					method: "get",
					success: function(result) {
						hideBox();
					}
				});
			}
		</script>
		{/literal}
		<link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='custom/plugins/CookieAcceptFrontend/Views/assets/css/cookie_frontend.css' fullPath}" />
		<div id="logout-bg" class="{if $hide_cookie_code_box} is--hidden{/if} slide-down">
			<div id="logout-wrapper">
				<div class="info-box">
					{$infoBox}
				</div>
				<div class="info-action-box">
					<div class="last cookie-accept-button">
						<button class="buybox--button block btn is--primary is--icon-right is--center is--large" name="Ok">
							{s namespace='frontend/index/cookie_frontend_index' name='Ok'}Ok{/s} <i class="icon--arrow-right"></i>
						</button>
					</div>
				</div>
			</div>	
		</div>
	{/if}
{/block}
