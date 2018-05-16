This module contains code to track training views that depends on Redis.

While Redis is disabled, this module just alters the existing history module JS.

Since Redis is not a declared dependency, do not try to use this module's built in tracking functions without enabling Redis. 