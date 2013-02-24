<!--
Facebook APP ID para PRESTASHOP
-->
<div id="fb-root"></div>
{literal}
    <script>
      window.fbAsyncInit = function() {
        FB.init({appId: '{/literal}{$id_appFb}{literal}', status: true, cookie: true,
                 xfbml: true});
      };
      (function() {
        var e = document.createElement('script');
        e.type = 'text/javascript';
        e.src = document.location.protocol +
          '//connect.facebook.net/es_LA/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
{/literal}