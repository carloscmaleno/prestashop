<div class="product_social">

	<a href="{$base_dir}modules/sendtoafriend/sendtoafriend-form.php?id_product={$product->id}">
		<img height=30 border="0" src=" {$base_dir}/modules/blocksocialbuttons/img/email.jpeg" alt="email" title="Enviar por email" /></a>
		
	<a target="_blank" href="http://twitter.com/home?status= {$base_dir}{$product->id}-{$product->link_rewrite}.html" title="Compartir articulo Twitter">
		<img height=30 border="0" src="{$base_dir}/modules/blocksocialbuttons/img/twiter.jpeg" alt="twitter" title="Compartir en Twiter" /></a>
		
	<a target="_blank" href="http://www.tuenti.com/share?url={$base_dir}{$product->id}-{$product->link_rewrite}.html">	
		<img height=30 border="0" src="{$base_dir}/modules/blocksocialbuttons/img/tuenti.jpeg" alt="tuenti" title="Compartir en Tuenti" /></a>
		
	<a target="_blank" href="http://www.facebook.com/share.php?u={$base_dir}{$product->id}-{$product->link_rewrite}.html">	
		<img height=30 border="0" src="{$base_dir}/modules/blocksocialbuttons/img/facebook.jpeg" alt="facebook" title="Compartir en Facebook" /></a>
		
	<g:plusone size="standar" count="false" href="{$base_dir}{$product->id}-{$product->link_rewrite}.html"></g:plusone>
	
	<iframe src="http://www.facebook.com/plugins/like.php?href={$base_dir}{$product->id}-{$product->link_rewrite}.html" 
		scrolling="no" 
		frameborder="0"
      style="border:none; width:80px; height:24px">
   </iframe>
				
</div>
