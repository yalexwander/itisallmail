# Supported options

## Introdution

Each option can be specified for all app, for separate driver, for separate source. 

# Option list

### mailbox_base_dir

Specifies the path to directory where all maildirs will be stored. App stores all maildirs in one place.
Can be set for: app, driver, source.

### mailbox

Default mailbox name to put all downloaded posts if mailbox is unspecified.
Can be set for: app, driver, source.

## catalog_mailbox

Default mailbox to store temporary messages download by `catalog` action. Workflow assumes that this mailbox is cleaned each time `catalog` action called.

Can be set for: app, driver, source.


### download_attachements

Allowed values: `full` | `thumb` | `none`

Description:

Specifies if files attached to post, or linked from post, or somewhere else related to post should be downloaded. 
`full` means full files downloaded. 
`thumb` means thumbnails for videos or big images downloaded.
`none` means no related files will be downloaded.

Can be set for: app, driver, source.

### update_interval

Allowed values: integer

Update interval for monitor script in seconds. Specifies how much to sleep between fetching each source.


### change_subject_if_attachements

Allowed values: 1 | 0

If set, `Subject` field for each message will have a mark, that message has attached files.

### nickname_transformation

Allowed values: `readable` | `strict` | `strict_name`

Specifies the way nicknames of users who create posts transformed to email addresses. Email address has limits on which characters can be included in it. And many different email software has own limitations on working with it. So you must choose a compromise - readability vs support by email software.
`readable` tries to keep nicknames as close to originals as possible.
`strict` use uuencode to keep nicknames supportable by email software. Best mode if you plan to use email client to send messages.
`strict_name` use uuencode for email address and full nickname in description.


### drivers

Allowed values: list of strings

Specifies which drivers should be loaded when calling fetcher script.

Can be set for: app.
