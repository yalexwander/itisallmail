# Fetcher

`scripts/fetcher.php` used to fetch messages from single source by its url/id. Source must be listed in `conf/sources.yml` . Fetched messages converted to emails and put to corresponding directories. To see debug output start script this way:

    CIM_DEBUG=1 php scripts/fetcher.php <url>
    

## Usage

    php scripts/fetcher.php <url>
