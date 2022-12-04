This is simple driver for reading RSS feeds from mailbox.

1) Add driver to config.yml:

```
drivers :
  - "rss"
```

2) Add source in sources.yml:

```
- url: https://some.site/feed/rss
  driver: rss
  mailbox_base_dir: /tmp
  mailbox: mailbox_rss
```

Setting `driver` option to "rss" is important, or URL can be processed by a wrong driver.
