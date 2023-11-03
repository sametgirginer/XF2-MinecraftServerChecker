<?php

namespace MinecraftServerChecker;

class Cron 
{
    public static function getData()
    {
        $db = \XF::db();
        $client = \XF::app()->http()->client(['headers' => ['Accept' => 'application/json']]);
        $servers = $db->query('SELECT * FROM xf_msc_servers')->fetchAll();
        
        if ($servers) {
            foreach ($servers as $server) {
                $thread = $db->query('SELECT * FROM xf_thread WHERE thread_id = ?', $server['thread_id'])->fetch();

                if (!$thread) {
                    $db->query('DELETE FROM xf_msc_servers WHERE thread_id = ?', $server['thread_id']);
                } else if ($thread['discussion_state'] === "visible") {
                    $data = Server::getServerData($server['ip']);            
                    $db->query('UPDATE xf_msc_servers SET status = ?, online = ?, max = ?, last_update = ? WHERE thread_id = ?', [$data['status'], $data['online'], $data['max'], time(), $server['thread_id']]);
                } else {
                    $db->query('UPDATE xf_msc_servers SET status = ?, online = ?, max = ?, last_update = ? WHERE thread_id = ?', [0, 0, 1, time(), $server['thread_id']]);
                }
            }
        }
    }
}