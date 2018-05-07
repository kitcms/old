<?php
/*
 * ...
 *
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Copyright (c) 2017 KIT Ltd
 */

namespace Vesta;

class Api
{
    protected $hostname;
    protected $hash;
    protected $port;

    use User;

    public function __construct($hostname, $hash, $port = 8083)
    {
        $vars = get_defined_vars();
        foreach ($vars as $key => $value) {
            $this->{$key} = $value;
        }
    }

    private function prepareArgs(array $params = array())
    {
        $params = array_values($params);
        foreach($params as $key => $value) {
            unset($params[$key]);
            $params['arg'. ++$key] = $value;
        }
        return $params;
    }

    private function send(array $params = array())
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $backtrace = next($backtrace);
        $function = preg_replace('/([a-z])([A-Z])/', '$1-$2', $backtrace['function']);
        $params = array_merge(
            array(
                'hash' => $this->hash,
                'cmd' => 'v-'. strtolower($function)
            ),
            $params
        );
        $query = http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://'. $this->hostname .':'. $this->port .'/api/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        $answer = curl_exec($curl);
        if ('json' === end($params)) {
            $answer = json_decode($answer, true);
        }
        return $answer;
    }
}
