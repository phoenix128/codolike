<?php
/**
 * CodoLike
 * @copyright  Copyright (c) 2015 Riccardo Tempesta (http://www.riccardotempesta.com)
 */

function codolike_add_like($user, $postId) {
    $postId = intval($postId);
    $userId = intval($user->id);

    if (!$userId)
        return array('success' => false, 'message' => 'You must be logged in');

    // Search post
    $q = codolike::$db->query(
        "select "
            ."p.*, t.title, t.cat_id, t.uid as tuid "
        ."from ".codolike::$db_prefix."posts as p "
        ."inner join ".codolike::$db_prefix."topics as t "
        ."on p.topic_id = t.topic_id "
        ."where p.post_id=$postId"
    );

    $postInfo = $q->fetch();

    if (!$postInfo || !$postInfo['post_id'])
        return array('success' => false, 'message' => 'Post not found');

    $q = codolike::$db->query("select * from codolike_likes where post_id=$postId and user_id=$userId");
    $res = $q->fetch();
    if ($res && $res['post_id'])
        return array('success' => false, 'message' => 'You already clicked like for this post');

    $q = codolike::$db->prepare("insert into codolike_likes(post_id, user_id, created_at, ip) values(:postid, :userid, NOW(), :ip)");
    $q->execute(array(
        ':postid' => $postId,
        ':userid' => $userId,
        ':ip' => $_SERVER['REMOTE_ADDR'],
    ));

    $likeData = array(
        "cid" => $postInfo['cat_id'],
        "tid" => $postInfo['topic_id'],
        "tuid" => $postInfo['tuid'],
        "puid" => $postInfo['uid'],
        "actor" => array(
            "username" => $user->name,
            "id" => $user->id,
            "role" => \CODOF\User\User::getRoleName($user->rid),
            "avatar" => $user->rawAvatar
        ),
        "message" => \CODOF\Util::start_cut(codolike::s('A new like from '.$user->name.' on you post'), 120),
        "pid" => $postInfo['post_id'],
        "mentions" => array(),
        "title" => $postInfo['title'],
    );

    $notifier = new \CODOF\Forum\Notification\Notifier();
    $notifier->queueNotify('new_like', $likeData);

    \CODOF\Hook::call('after_like_post', $likeData);

    return array('success' => true, 'count' => intval($res['c']));
}

function codolike_get_likes($userId, $ids)
{
    $userId = intval($userId);

    $ids2 = array();
    $ids = explode(',', $ids);

    foreach ($ids as $id) $ids2[] = intval($id);

    if (!count($ids2)) return array();

    $q = codolike::$db->query("select post_id, COUNT(*) as c from codolike_likes where post_id in (".implode(',', $ids2).") group by post_id");
    $res = $q->fetchAll();

    $likes = array();
    foreach($res as $i) $likes[$i['post_id']] = intval($i['c']);

    $return2 = array();
    foreach($ids2 as $i)
    {
        $q = codolike::$db->query("select codo_users.id, codo_users.name from codolike_likes inner join codo_users on codo_users.id = codolike_likes.user_id where post_id=".$i);
        $res = $q->fetchAll();

        $users = array();
        foreach ($res as $j) $users[] = array('id' => $j['id'], 'name' => $j['name']);

        $return2[$i] = array(
            'count' => count($users),
            'users' => $users,
        );
    }

    return $return2;
}
