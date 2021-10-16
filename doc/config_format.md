# Configuration options

These options are available for setting in /conf/config.yml

### mailbox

Default value: "mailboxes/default". Default mailbox in maildir format where all messages from all sources will be placed, it another one is not specified for specific source.

### mailbox_base_dir

Default value: "mailboxes" . Specifies the absolute directory path where will be placed all mailboxes. If you will specify mailbox "news" for some source, the program will try to save messages into "<program_path>/mailboxes/news".

### update_interval

Default value: 300. Specifies the period between each fetch of new messages. Please note, that this value is not exact - the more sources you have, the more interval will be between fetching the source.


### drivers

List of drivers used in application. Driver names must be the same as direcory name in `/lib/ItIsAllMail/Driver`. Each driver can have own settings. For example:

    drivers :
        - "Forumhouse" :
          - "mailbox" : "mailboxes/forumhouse.ru"
          - "proxy" : {}
          - "account : {}
