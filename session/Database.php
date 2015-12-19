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

use PDOException;
use SessionHandlerInterface;
use Opis\Database\Connection;
use Opis\Database\Database as OpisDatabase;

class Database implements SessionHandlerInterface
{
    /** @var    int */
    protected $maxLifetime;

    /** @var    \Opis\Database\Database */
    protected $db;

    /** @var    string */
    protected $table;

    /** @var    array */
    protected $columns;

    /**
     * Constructor
     * 
     * Database storage requires a table with three columns: id, data and expires.
     * It is recommended that `id` column to be unique and `data` column blob.
     * You can change columns name by passing the fourth parrameter.
     * 
     * @param   \Opis\Database\Connection   $connection     Database connection
     * @param   string                      $table          Table's name
     * @param   int                         $maxLifetime    (optional) Session's max lifetime
     * @param   array                       $columns        (optional) Table's columns
     */
    public function __construct(Connection $connection, $table, $maxLifetime = 0, array $columns = array())
    {
        $this->db = new OpisDatabase($connection);
        $this->table = $table;
        $this->maxLifetime = $maxLifetime > 0 ? $maxLifetime : ini_get('session.gc_maxlifetime');

        $columns += array(
            'id' => 'id',
            'data' => 'data',
            'expires' => 'expires',
        );

        $this->columns = $columns;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // Fixes issue with Debian and Ubuntu session garbage collection

        if (mt_rand(1, 100) === 100) {
            $this->gc(0);
        }
    }

    /**
     * Open session.
     *
     * @param   string   $savePath     Save path
     * @param   string   $sessionName  Session name
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
     * @param   string  $id  Session id
     * 
     * @return  string
     */
    public function read($id)
    {
        try {
            $result = $this->db->from($this->table)
                ->where($this->columns['id'])->eq($id)
                ->column($this->columns['data']);

            return $result === false ? '' : $result;
        } catch (PDOException $e) {
            return '';
        }
    }

    /**
     * Writes data to the session.
     *
     * @param   string  $id    Session id
     * @param   string  $data  Session data
     * 
     * @return  boolean
     */
    public function write($id, $data)
    {
        try {
            $result = $this->db->from($this->table)
                ->where($this->columns['id'])->eq($id)
                ->count();

            if ($result != 0) {
                return (bool) $this->db->update($this->table)
                        ->where($this->columns['id'])->eq($id)
                        ->set(array(
                            $this->columns['data'] => $data,
                            $this->columns['expires'] => time() + $this->maxLifetime,
                ));
            } else {
                return $this->db->insert(array(
                            $this->columns['id'] => $id,
                            $this->columns['data'] => $data,
                            $this->columns['expires'] => time() + $this->maxLifetime
                        ))
                        ->into($this->table);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Destroys the session.
     *
     * @param   string   $id  Session id
     * @return  boolean
     */
    public function destroy($id)
    {
        try {
            return (bool) $this->db->from($this->table)
                    ->where($this->columns['id'])->eq($id)
                    ->delete();
        } catch (PDOException $e) {
            return false;
        }
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
        try {
            return (bool) $this->db->from($this->table)
                    ->where($this->columns['expires'])->lt(time())
                    ->delete();
        } catch (PDOException $e) {
            return false;
        }
    }
}
