This is a local driver used to convert local Viber sqlite database to Maildir. You need Viber being installed, or at least have Viber profile dir.

To convert:

1) Add driver to config.yml:

```
drivers :
  - "viber.local"
```

2) Add source in sources.yml:

```
- url: viber.local:/home/user/.ViberPC/12312321125
  mailbox_base_dir: /tmp
  mailbox: mailbox_viber
```

12312321125 - is basically your number withou leading plus. Or any ID Viber use to create directory.
