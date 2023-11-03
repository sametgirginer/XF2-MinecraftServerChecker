<?php 

namespace MinecraftServerChecker;

class Server
{
    public static function getServerData(String $server_ip) 
    {
        $api = \XF::options()->msc_api;
        $client = \XF::app()->http()->client(['headers' => ['Accept' => 'application/json']]);

        $status = 0;
        $online = 0;
        $max = 1;

        if ($api === "mcsrvstatus") {
            $response = $client->get('https://api.mcsrvstat.us/2/' . $server_ip);
            $rawdata = \GuzzleHttp\json_decode($response->getBody(), true);
            
            $status = ($rawdata['online'] === true) ? 1 : 0;
            if ($rawdata['online']) {
                $online = $rawdata['players']['online'];
                $max = $rawdata['players']['max'];
            }
        } else {
            $response = $client->get('https://api.keyubu.net/minecraft/json.php?host=' . $server_ip . '&port=25565');
            $rawdata = \GuzzleHttp\json_decode($response->getBody(), true);
            
            $status = ($rawdata['online'] === true) ? 1 : 0;
            if ($rawdata['online']) {
                $online = $rawdata['players']['online'];
                $max = $rawdata['players']['max'];
            }
        }
        
        $data = [
            'status' => $status,
            'online' => $online,
            'max' => $max
        ];

        return $data;
    }
}