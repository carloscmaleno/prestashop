<!-- MODULE Block fanpage -->

<div class="block blockfanpage">
	<h4 style="font-size: 1.1em;">Síguenos en</h4>		
	<div class="block_content" style="padding: 5px 0 0 5px;text-align: center;">
		{if ($facebook != '')}<a title="{l s='Síguenos en Facebook'}" target="_blank" href="{$facebook}"><img title="{l s='Síguenos en Facebook'}" alt="Facebook" src="{$blockfanpage_img}facebook.png"></a>{/if}
		{if ($twitter != '')}<a title="{l s='Síguenos en Twitter'}" target="_blank" href="{$twitter}"><img title="{l s='Síguenos en Twitter'}" alt="Twitter" src="{$blockfanpage_img}twitter.png"></a>{/if}
		{if ($tuenti != '')}<a title="{l s='Síguenos en Tuenti'}" target="_blank" href="{$tuenti}"><img title="{l s='Síguenos en Tuenti'}" alt="Tuenti" src="{$blockfanpage_img}tuenti.png"></a>{/if}
		{if ($gplus != '')}<a title="{l s='Síguenos en Google Plus'}" target="_blank" href="{$gplus}"><img title="{l s='Síguenos en Google Plus'}" alt="Google Plus" src="{$blockfanpage_img}gplus.png"></a>{/if}
		{if ($linkedin != '')}<a title="{l s='Síguenos en LinkedIn'}" target="_blank" href="{$linkedin}"><img title="{l s='Síguenos en LindedIn'}" alt="Linkedin" src="{$blockfanpage_img}linkedin.png"></a>{/if}		 
	</div>		
</div>

<div class="block blockfanfacebook" style="height:320px;">
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return ;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/es_ES/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
 
	<div class="fb-like-box" data-header="false" data-href="{$facebook}" data-width="190" data-height="320" data-connections="9" data-show-faces="true" data-stream="false" data-header="true"></div>
</div>

<!-- /MODULE Block fanpage -->