<!--
Facebook Comentarios para prestashop
Puedes configurar el Numero de Post por pagina (numposts="10") y el ancho de la columna a mostrar (width="550")
-->
<div id="tab_facebook_comentarios">
    <p><fb:comments href="{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" num_posts="{if ($no_Fb_comentarios < 0)}10{else}{$no_Fb_comentarios}{/if}" width="{if ($ancho_comentarios < 0)}500{else}{$ancho_comentarios}{/if}"></fb:comments></p>
    </div>