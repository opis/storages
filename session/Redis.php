<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Session\Storage;

use Predis\Client;
use SessionHandlerInterface;

class Redis implements SessionHandlerInterface
{
    /** @var    string */
    protected $prefix;

    /** @var    int */
    protected $maxLifetime;

    /** @var    \Predis\Client */
    protected $redis;

    /**
     * Constructor
     *
     * @param   \Predis\Client  $redis          Redis client
     * @param   string          $prefix         (optional) Prefix
     * @param   int             $maxLifetime    (optional) Session's max lifetime
     */
    public function __construct(Client $redis, $prefix = 'session_', $maxLifetime = 0)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->maxLifetime = $maxLifetime > 0 ? $maxLifetime : ini_get('session.gc_maxlifetime');
    }

    /**
     * Open session.
     *
     * @param   string  $savePath       Save path
     * @param   string  $sessionName    Session name
     * 
     * @return  boolean
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Close session.
     *
     * @return  boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Returns session data.
     *
     * @param   string  $id Session id
     * 
     * @return  string
     */
    public function read($id)
    {
        return (string) $this->redis->get($this->prefix . $id);
    }

    /**
     * Writes data to the session.
     *
     * @param   string  $id     Session id
     * @param   string  $data   Session data
     *
     * @return   boolean
     */
    public function write($id, $data)
    {
        $this->redis->set($this->prefix . $id, $data);

        $this->redis->expire($this->prefix . $id, $this->maxLifetime);

        return true;
    }

    /**
     * Destroys the session.
     *
     * @param   string   $id  Session id
     * 
     * @return  boolean
     */
    public function destroy($id)
    {
        return (bool) $this->redis->del($this->prefix . $id);
    }

    /**
     * Garbage collector.
     *
     * @param   int      $maxLifetime  Lifetime in secods
     * 
     * @return  boolean
     */
    public function gc($maxLifetime)
    {
        return true;
    }
}
