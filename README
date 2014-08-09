APC2Memcached : Easy Migration from APC User Cache to Memcached
===============================================================

APC2Memcached is an emulation layer that attempts to reproduce the exact behavior
of every APC user cache function while using Memcached as the backend.
It allows legacy PHP applications that depend on the APC user cache
to run seamlessly in environments where the APC extension is not available.

But Why?
--------

APC is not compatible with PHP 5.5, and has several problems when used with PHP 5.4.
The user cache is available as a separate extension (APCu),
but this extension is often missing from official repositories for Linux distributions,
and who knows how long it's going to be maintained anyway?

The long-term solution is to migrate to a dedicated caching backend such as Memcached.
However, it is not easy to migrate an existing project to a different caching backend,
especially if your codebase is peppered with numerous calls to APC functions all over the place.
In addition, Memcached often behaves in a different way from APC,
so you can't just search and replace all APC function calls with Memcached equivalents.

APC2Memcached is a band-aid solution to get around this minor annoyance.
Like every emulation layer, it is slower than using either APC or Memcached directly.
But if your choice is between APC2Memcached and no caching at all, APC2Memcached will be faster.

Function Reference
------------------

To start using APC2Memcached, initialize it as follows.
You can omit the host and port, in which case they will default to 127.0.0.1 port 11211.

    APC2Memcached::connect($host, $port);

To inject a Memcached object that you have already instantiated and configured, use the inject() method instead.
This can be useful if you would like to use a pool with multiple servers or need to use a special configuration.

    APC2Memcached::inject($memcached);

After that, feel free to use the following functions as if they were provided by the APC extension:

  - apc_fetch()
  - apc_store()
  - apc_add()
  - apc_delete()
  - apc_exists()
  - apc_cas()
  - apc_inc()
  - apc_dec()
  - apc_clear_cache()
  - apc_define_constants()
  - apc_load_constants()

APC2Memcached also defines the following functions, but they do nothing:

  - apc_cache_info()
  - apc_sma_info()
  - apc_compile_file()
  - apc_delete_file()

Please refer to the official APC documentation [1] and the official Memcached documentation [2] for further details.

Some of the functionality (such as compare-and-swap) that is atomic in APC may not be atomic in Memcached,
due to incompatibilities between the two interfaces.
(For example, CAS in Memcached means something completely different.)

Summary
-------

tl;dr: APC2Memcached makes Memcached behave like APC.
If you want to make APC behave like Memcache instead (note the missing 'd'), try OOAPC [3].

  - [1] http://php.net/manual/en/ref.apc.php
  - [2] http://php.net/memcached
  - [3] https://github.com/kijin/ooapc
