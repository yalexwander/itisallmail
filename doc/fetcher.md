# Fetcher

To fetch messages from all sources specified in `conf/sources.yml`
script `scripts/fetcher.php` must be started. Fetched messages
converted to emails and put to corresponding directories. To see debug
output start script this way:

    CIM_DEBUG=1 php scripts/fetcher.php
