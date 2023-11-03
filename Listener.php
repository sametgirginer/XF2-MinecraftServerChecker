<?php 

namespace MinecraftServerChecker;

use XF\Mvc\Entity\Entity;
use XF\Template\Templater;
use MinecraftServerChecker\Server;

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

        if (!isset($old_server_ip['msc_server_ip']) || !isset($new_server_ip['custom_fields'])) return;

        if ($old_server_ip) $server_ip = $old_server_ip['msc_server_ip'];
        else if ($new_server_ip) $server_ip = $new_server_ip['custom_fields']['msc_server_ip'];

        if ($server_ip) {
            $server_ip = strtolower($server_ip);
            $data = Server::getServerData($server_ip);

            if (!$thread && $server_ip) {
                $db->insert('xf_msc_servers', [
                    'thread_id'     => $thread_id,
                    'ip'            => $server_ip,
                    'status'        => $data['status'],
                    'online'        => $data['online'],
                    'max'           => $data['max'],
                    'last_update'   => time(),
                ]);
            } else if ($thread && $server_ip && $new_server_ip) {
                $db->query('UPDATE xf_msc_servers SET ip = ?, status = ?, online = ?, max = ? WHERE thread_id = ?', [$server_ip, $data['status'], $data['online'], $data['max'], $thread_id]);
            }
        } else if ($thread && !$server_ip) {
            $db->query('DELETE FROM xf_msc_servers WHERE thread_id = ?', $thread_id);
        }
    }

    public static function templaterMacroPreRender(Templater $templater, &$type, &$template, &$name, array &$arguments, array &$globalVars)
    {
        $thread_id = null;
        $msc_title = false;

        if ($name === "thread_list") {
            $thread_id = ($globalVars['thread']) ? $globalVars['thread']->thread_id : null;
        } else if ($name === "thread_view") {
            $thread_id = ($globalVars['constraint']['c']['thread'] && !$globalVars['thread']) ? $globalVars['constraint']['c']['thread'] : null;
            $msc_title = true;
        }

        if ($thread_id === null) return;
        
        $db = \XF::Db();
        $server = $db->query('SELECT * FROM xf_msc_servers WHERE thread_id = ?', $thread_id)->fetch();
        $status = ($server['status']) ? 'online' : 'offline';

        $templater->addDefaultParams([
            'msc_ip' => $server['ip'],
            'msc_status' => $status,
            'msc_online' => $server['online'],
            'msc_max' => $server['max'],
            'msc_title' => $msc_title,
        ]);
    }
}