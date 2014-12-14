<?php
/**
 * CodoLike
 * @copyright  Copyright (c) 2015 Riccardo Tempesta (http://www.riccardotempesta.com)
 */

require_once('adapters/Codoforum.php');
require_once('arg.php');
require_once('server/codolike.php');

$codoLikeAdapter = new CodoLikeAdapter();
$codoLikeAdapter->setup_tables();
codolike::$path = $codoLikeAdapter->get_abs_path();
codolike::$db_prefix = PREFIX . 'codo_';
codolike::$like_post_path = RURI . 'codolike/like';
codolike::$like_update_path = RURI . 'codolike/update';
codolike::$db = \DB::getPDO();

function codolike_add_assets() {

    $codoLikeAdapter = new CodoLikeAdapter();
    $codoLikeAdapter->add_css(codolike::$path . "client/css/app.css");
    $codoLikeAdapter->add_js("http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
    $codoLikeAdapter->add_js(codolike::$path . "client/js/app.js");
}


function codolike_like_js() {

    include("block/like_js.php");
}

function codolike_body_start() {

    include('block/body_start.php');

}

function codolike_after_notify_insert() {
    $notifier = new \CODOF\Forum\Notification\Notifier();

    $res = codolike::$db->query(
        "SELECT "
            ."q.id, q.nid, t.data, q.type "
        ."FROM ".PREFIX."codo_notify_queue as q "
        ."INNER JOIN codo_notify_text as t on "
            ."q.nid=t.id "
        ."where q.type='new_like'");

    $queue = $res->fetchAll();
    foreach ($queue as $queuedLike) {
        $data = json_decode($queuedLike['data'], true);
        $notifier->notify(array($data['puid']), $queuedLike['type'], $queuedLike['nid']);

        // Automatically dequeue notification
        codolike::$db->query("delete from ".PREFIX."codo_notify_queue where id=".intval($queuedLike['id']));
    }
}

\CODOF\Hook::add('block_body_start', 'codolike_body_start');
\CODOF\Hook::add('block_topic_info_after', 'codolike_like_js');
\CODOF\Hook::add('tpl_before_forum_topic', 'codolike_add_assets');
\CODOF\Hook::add('after_like_post', 'codolike_after_notify_insert'); // This runs just before item queue deletion
//\CODOF\Hook::add('on_cron_notify_new_like', array(new \CODOF\Forum\Notification\Notifier, 'dequeueNotify'));

dispatch("codolike/like", function(){
    $codoLikeAdapter = new CodoLikeAdapter();
    echo json_encode(codolike_add_like($codoLikeAdapter->get_user(), $_GET['id']));
});

dispatch("codolike/update", function(){
    $codoLikeAdapter = new CodoLikeAdapter();
    echo json_encode(codolike_get_likes($codoLikeAdapter->get_user()->id, $_GET['ids']));
});