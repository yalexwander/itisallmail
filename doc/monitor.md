# Monitor

`scripts/monitor.php` is used for regular downlading of new posts. When started it calculates next update time for each source from `conf/sources.yml` and starts `scripts/fetcher.php` to process it.


## Usage

    php scripts/monitor.php
