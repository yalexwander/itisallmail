# ItIsAllMail

A tool to parse forums, imageboards, all sites with discussions, and
even chats and messengers to a mailbox format. Once you've got your
data into a maildir, you can use it with all mail processing tools.

# Usage and basic concepts

ItIsAllMail highly rely on Neomutt mail client, but can be used with any mail client, but with limited features. Each forum thread, telegram chat etc. is treated as "Source". Source identified by its URI. Each source can be configured in unique way, like update interval, prxoxy, mail converting options etc.

Many options can be defined on 3 levels:

 - app level, in `conf/config.yml`
 - driver level, in corresponding `lib/ItIsAllMail/Driver/<driver>/driver.cfg`
 - source level, in corresponsing entry of `conf/sources.yml`

Option value defined in source has more priority than driver option, and the driver option has more priority over app option.

So to start you need to add few sources into `conf/sources.yml`, and then start `scripts/monitor.php` from the app root dir. Then you will get all messages from source converted into emails and put into `mailboxes/default/`. From there you can use any mail client which support maildir format, like:

- neomutt
- mu4e
- thunderbird
- clawsmail

Or you can serve this maildir using POP3 or IMAP server, for example Dovecot.

You can reply on these emails messages. So when you create an email in respond to some tweet or telegram message, the message will be shown for all users of tweetter or telegram.

## Example screenshots

### A web page with tree discussion:

![Web page](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_website.png)


### Same discussion in representation of maildir, opened in Neomutt:
![Comments as thread](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_discussion.png)
![Article or post](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_message_view.png)


## Usage

1. Dependencies for Debian-based distributions:

        mkdir itisallmail && cd itisallmail
        git clone https://github.com/yalexwander/itisallmail .
        apt install php-yaml php-xml composer
        composer update

2. Init the config from sample files

        cd conf
        cp sources.yml.example sources.yml
        cp config.yml.example config.yml
        
3. By default all mail is placed into `./mailboxes/default`. You can
   set separate maildir for each source with `mailbox` section in
   source file. Do not forget to create folders `new` and `cur` inside
   each new mailbox.

4. Run script to fetch sources and ensure all works properly.

        php scripts/fetcher.php
        
5. You can check if messages converted for example with Neomutt mail
   client:

        neomutt -f maildir
        
6. If everything works properly, you can add `fetcher.php` to cron, or
   use monitor.php, which will start fetcher periodically.
        

## Adding driver for another site

To add new site follow such steps:

1) See file `lib/ItIsAllMail/Driver/Dummy/Fetcher.php` as an template.

2) In `conf/config.yml` add the driver directory name into section
`drivers`
