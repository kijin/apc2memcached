<?php
/**
* APC2Memcached : Easy Migration from APC User Cache to Memcached
*
* URL: http://github.com/kijin/apc2memcached
* Version: 0.1.1
*
* Copyright (c) 2013-2014, Kijin Sung <kijin@kijinsung.com>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

class APC2MemcachedException extends Exception { }

// Check if APC is disabled and Memcached is enabled.

if (function_exists('apc_fetch'))
{
    throw new APC2MemcachedException('Cannot initialize APC2Memcached: APC extension is already enabled.');
}

if (!class_exists('Memcached'))
{
    throw new APC2MemcachedException('Cannot initialize APC2Memcached: Memcached extension is not enabled.');
}

// The main class that does all the work.

class APC2Memcached
{
    protected static $_con;
    
    // Directly connect to a Memcached server.
    
    public static function connect($host = '127.0.0.1', $port = 11211)
    {
        self::$_con = new Memcached;
        self::$_con->addServer($host, $port);
    }
    
    // Inject a pre-existing Memcached instance.
    
    public static function inject(Memcached $con)
    {
        self::$_con = $con;
    }
    
    // Fetch (get).
    
    public static function fetch($key, &$success)
    {
        $value = self::$_con->get($key);
        $success = ($value !== false);
        return $value;
    }
    
    // Store (set).
    
    public static function store($key, $value, $ttl = 0)
    {
        return self::$_con->set($key, $value, $ttl);
    }
    
    // Add (set only if the key does not already exist).
    
    public static function add($key, $value = null, $ttl = 0)
    {
        if (is_array($key))
        {
            $result = array();
            foreach ($key as $arrkey => $arrval)
            {
                $status = self::$_con->add($arrkey, $arrval, $ttl);
                if (!$status) $result[] = $arrkey;
            }
            return $result;
        }
        else
        {
            return self::$_con->add($key, $value, $ttl);
        }
    }
    
    // Delete.
    
    public static function delete($keys)
    {
        return is_array($keys) ? self::$_con->deleteMulti($keys) : self::$_con->delete($keys);
    }
    
    // Exists.
    
    public static function exists($keys)
    {
        if (is_array($keys))
        {
            $result = array();
            $status = self::$_con->getMulti($keys);
            foreach ($status as $arrkey => $arrval)
            {
                $result[$arrkey] = true;
            }
            return $result;
        }
        else
        {
            return (self::$_con->get($keys) !== false);
        }
    }
    
    // Compare and swap (this is not atomic).
    
    public static function cas($key, $old, $new)
    {
        if (strval(self::$_con->get($key)) === strval($old))
        {
            self::$_con->replace($key, $new);
            return true;
        }
        else
        {
            return false;
        }
    }
    
    // Increment.
    
    public static function incr($key, $step = 1, &$success = false)
    {
        $status = self::$_con->increment($key, $step);
        $success = ($status !== false);
        return $status;
    }
    
    // Decrement.
    
    public static function decr($key, $step = 1, &$success = false)
    {
        $status = self::$_con->decrement($key, $step);
        $success = ($status !== false);
        return $status;
    }
    
    // Clear the cache.
    
    public static function clear($cache_type)
    {
        if ($cache_type === 'user')
        {
            self::$_con->flush();
        }
        return true;
    }
    
    // Define constants.
    
    public static function defineConstants($key, $constants, $case_sensitive = true)
    {
        $status = self::$_con->set($key, $constants);
        foreach ($constants as $conkey => $conval)
        {
            $status = define($conkey, $conval, !$case_sensitive) && $status;
        }
        return $status;
    }
    
    // Load constants.
    
    public static function loadConstants($key, $case_sensitive = true)
    {
        $constants = self::$_con->get($key);
        $status = ($constants !== false);
        foreach ($constants as $conkey => $conval)
        {
            $status = define($conkey, $conval, !$case_sensitive) && $status;
        }
        return $status;
    }
}

// Functions to emulate APC.

function apc_fetch($key, &$success = false)
{
    return APC2Memcached::fetch($key, $success);
}

function apc_store($key, $value, $ttl = 0)
{
    return APC2Memcached::store($key, $value, $ttl);
}

function apc_add($key, $value = null, $ttl = 0)
{
    return APC2Memcached::add($key, $value, $ttl);
}

function apc_delete($keys)
{
    return APC2Memcached::delete($keys);
}

function apc_exists($keys)
{
    return APC2Memcached::exists($keys);
}

function apc_cas($key, $old, $new)
{
    return APC2Memcached::cas($key, $old, $new);
}

function apc_inc($key, $step = 1, &$success = false)
{
    return APC2Memcached::incr($key, $step, $success);
}

function apc_dec($key, $step = 1, &$success = false)
{
    return APC2Memcached::decr($key, $step, $success);
}

function apc_clear_cache($cache_type)
{
    return APC2Memcached::clear($cache_type);
}

function apc_define_constants($key, $constants, $case_sensitive = true)
{
    return APC2Memcached::defineConstants($key, $constants, $case_sensitive);
}

function apc_load_constants($key, $case_sensitive = true)
{
    return APC2Memcached::loadConstants($key, $case_sensitive);
}

function apc_cache_info()
{
    return array();
}

function apc_sma_info()
{
    return array();
}

function apc_compile_file()
{
    return false;
}

function apc_delete_file()
{
    return false;
}
