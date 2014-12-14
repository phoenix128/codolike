<?php

Schema::create('codolike_likes', function($table) {
    $table->increments('id');
    $table->integer('user_id');
    $table->integer('post_id');
    $table->dateTime('created_at')->default('0000-00-00 00:00:00');
    $table->string('ip', 255);
});

DB::update('create unique index post_uidx on codolike_likes(user_id, post_id)');
DB::update('create index post_idx on codolike_likes(post_id)');
