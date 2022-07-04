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
                    $response = $client->get('https://api.mcsrvstat.us/2/' . $server['ip']);
                    $data = \GuzzleHttp\json_decode($response->getBody(), true);
                    
                    $status = ($data['online'] === true) ? 1 : 0;
                    $online = 0;
                    $max = 1;
            
                    if ($data['online']) {
                        $online = $data['players']['online'];
                        $max = $data['players']['max'];
                    }
            
                    $db->query('UPDATE xf_msc_servers SET status = ?, online = ?, max = ?, last_update = ? WHERE thread_id = ?', [$status, $online, $max, time(), $server['thread_id']]);
                } else {
                    $db->query('UPDATE xf_msc_servers SET status = ?, online = ?, max = ?, last_update = ? WHERE thread_id = ?', [0, 0, 1, time(), $server['thread_id']]);
                }
            }
        }
    }
}