<?php
/**
 * CodoLike
 * @copyright  Copyright (c) 2015 Riccardo Tempesta (http://www.riccardotempesta.com)
 */
?>
<script type="text/javascript">
    CODOF.hook.add('on_scripts_loaded', function () {

        jQuery('.codo_posts_post_foot').each(function () {
            var $me = jQuery(this);
            $me.html('<div class="codo_like_users"></div>'+$me.html());
        });

        jQuery('.codo_posts_post_action').each(function () {

            var $me = jQuery(this);
            var html = $me.html();

            html = ''
                + '<div class="btn-group codo_like_btn_group">'
                    + '<div class="codo_btn_def codo_like_btn"><i class="glyphicon glyphicon-thumbs-up"></i></div>'
                    + '<div class="codo_btn_def codo_btn codo_like_counter"><img src="<?php echo codolike::$path ?>client/img/ajax-loader.gif" /></div>'
                    + '<div class="codo_btn_primary codo_btn codo_like_btn"><?php codolike::t('like') ?></div>'
                + '</div>'
                + html;

            $me.html(html);

            var postId = parseInt($me.parents('article').attr('id').replace('post-', ''));
            $me.children('.codo_like_btn_group').click(function () {
                jQuery('#post-'+postId+' .codo_like_counter').html('<img src="<?php echo codolike::$path ?>client/img/ajax-loader.gif" />');

                jQuery.getJSON( "<?php echo codolike::$like_post_path ?>&id="+postId, function(data) {
                    if (!data.success)
                        alert(data.message);

                    codolike_update_likes(postId);
                });
            });
        });

        var articles = new Array();
        jQuery('article').each(function () {
            var $me = jQuery(this);
            var postId = parseInt($me.attr('id').replace('post-', ''));

            articles.push(postId);
        });

        codolike_update_likes(articles);

        function codolike_update_likes(ids)
        {
            jQuery.getJSON( "<?php echo codolike::$like_update_path ?>&ids="+ids, function( data ) {
                jQuery.each(data, function(key, val) {
                    jQuery('#post-'+key+' .codo_like_counter').html(val.count);

                    var l = new Array();
                    for (var i=0; i<val.users.length; i++)
                        l.push('<a href="<?php echo RURI ?>user/profile/'+ val.users[i].id+'">'+val.users[i].name+'</a>')

                    if (l.length)
                        jQuery('#post-'+key+' .codo_like_users').html(<?php echo codolike::j('Likes to: ') ?>+'<span>'+l.join(', ')+'</span>');
                });
            });
        }
    });
</script>