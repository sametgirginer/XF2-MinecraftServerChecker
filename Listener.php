<?php 

namespace MinecraftServerChecker;

use XF\Mvc\Entity\Entity;
use XF\Template\Templater;

class Listener
{
    public static function threadPostSave(Entity $entity) 
    {
        $db = \XF::db();
        $thread_id = $entity->getValue('thread_id');
        $old_server_ip = $entity->getValue('custom_fields');
        $new_server_ip = $entity->getNewValues('custom_fields');
        $thread = $db->fetchOne('SELECT * FROM xf_msc_servers WHERE thread_id = ?', $thread_id);

        $server_ip = "";

        if ($old_server_ip) $server_ip = $old_server_ip['msc_server_ip'];
        else if ($new_server_ip) $server_ip = $new_server_ip['custom_fields']['msc_server_ip'];

        if ($server_ip) {
            $client = \XF::app()->http()->client(['headers' => ['Accept' => 'application/json']]);
            $response = $client->get('https://api.mcsrvstat.us/2/' . $server_ip);
            $data = \GuzzleHttp\json_decode($response->getBody(), true);
            
            $server_ip = strtolower($server_ip);
            $status = ($data['online'] === true) ? 1 : 0;
            $online = 0;
            $max = 1;

            if ($data['online']) {
                $online = $data['players']['online'];
                $max = $data['players']['max'];
            }

            if (!$thread && $server_ip) {
                $db->insert('xf_msc_servers', [
                    'thread_id'     => $thread_id,
                    'ip'            => $server_ip,
                    'status'        => $status,
                    'online'        => $online,
                    'max'           => $max,
                    'last_update'   => time(),
                ]);
            } else if ($thread && $server_ip && $new_server_ip) {
                $db->query('UPDATE xf_msc_servers SET ip = ?, status = ?, online = ?, max = ? WHERE thread_id = ?', [$server_ip, $status, $online, $max, $thread_id]);
            }
        } else if ($thread && !$server_ip) {
            $db->query('DELETE FROM xf_msc_servers WHERE thread_id = ?', $thread_id);
        }
    }

    public static function templaterMacroPreRender(Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
    {
        $thread_id = $globalVars['thread']->thread_id;
        
        $db = \XF::Db();
        $server = $db->query('SELECT * FROM xf_msc_servers WHERE thread_id = ?', $thread_id)->fetch();
        $status = ($server['status']) ? 'online' : 'offline';

        $templater->addDefaultParams([
            'msc_ip' => $server['ip'],
            'msc_status' => $status,
            'msc_online' => $server['online'],
            'msc_max' => $server['max'],
        ]);
    }
}