# Sources file

Located in `conf/sources.yml` the list of sources to fetch from and transform to
maildir. It is plain YAML file. See the `conf/sources.yml.example`

For each source entry you must at least specify `url` field. `code` is optional.
Defaul `mailbox` for all sources is `mailboxes/default`.


## List of allowed fields

`url` - required, also the ID for each source
`mailbox` - full path to folder in maildir format where all messages from give source will be saved
