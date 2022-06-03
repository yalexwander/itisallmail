# Supported options

## Introdution

Each option can be specified for all app, for separate driver, for separate source. 

# Option list

### mailbox_base_dir

Specifies the path to directory where all maildirs will be stored. App stores all maildirs in one place.
Can be set on level of: app, driver, source.

### mailbox

Default mailbox name to put all downloaded posts if mailbox is unspecified.
Can be set on level of: app, driver, source.

## catalog_mailbox

Default mailbox to store temporary messages download by `catalog` action. Workflow assumes that this mailbox is cleaned each time `catalog` action called.

Can be set on level of: app, driver, source.


### download_attachements

Allowed values: `full` | `thumb` | `none`

Description:

Specifies if files attached to post, or linked from post, or somewhere else related to post should be downloaded. 
`full` means full files downloaded. 
`thumb` means thumbnails for videos or big images downloaded.
`none` means no related files will be downloaded.

Can be set on level of: app, driver, source.

### source_update_interval

Allowed values: integer

Update interval for single source in seconds. Specifies how much to sleep between fetching each source. Even if sources handled by different drivers.

Can be set on level of: app, driver, source.

### between_source_update_interval

Allowed values: integer

Threshold value to prevent calling updates too often. This option sets the least number of seconds should pass before downloading next source. While it can be set on driver level, the main idea is to threshold the frequency of requests for monitor.

Can be set on level of: app, driver, source.

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

Can be set on level of: app.


### drivers

Allowed values: list of strings

Specifies which drivers should be loaded when calling fetcher script.

Can be set on level of: app.

### fetcher_proxy

Allowed values: string

If blank string, no proxy program for fetching sources used. Otherwise you can specify for example "torsocks" or "proxychains".

Can be set on level of: app, driver, source.


### poster_proxy

Allowed values: string

If blank string, no proxy program for posting used. Otherwise you can specify for example "torsocks" or "proxychains"

Can be set on level of: app, driver, source.
