# ItIsAllMail

This software claims to be a bridge between your local mailbox(and email clients) and any other source of information like:

- web forums and bulletin boards
- telegram, discord, facebook, etc
- anything that you want to handle via your email client

# Usage and basic concepts

The core conception of ItIsAllMail is a source. Source is a shortcut for telegram chat, forum thread, reddit thread, main page of news site, github issues page etc. There is a `scripts/monitor.php` which monitors sources list from file `conf/sources.yml` for changes. Source is identified by `url` key.

All new messages from source converted to email messages in MIME format and stored to corresponding maildir. Then you can choose how to operate with it. You can use some GUI email clients like Thunderbird and Clawsmail to just read new messages avoid using browser. Or you can use more sophisticated clients like Mutt/Neomutt to do posting on site/chat via email client if it is supported by a driver.

To support different sources of information different drivers used. Each driver has few parts:

- Fetcher resposnds for fetching new messages from source. It is the most required and basic part which fetches web page or queries a chat for new messages and then transform it into an unified message format. This unified message is dumped to maildir by ItIsAllMail using settings for a given source.

- Catalog responds for quering information from site. There is a special maildir called catalog shipped with ItIsAllMail. Catalag driver part can be called from CLI or from client like Neomutt and list of potential sources. It works like search for channels in IRC or Telegram, but the list of channels is dumped to list of emails. Then you can select one of such message and add as a source, like if you subscribe/join that channel but in terms of ItIsAllMail. Usually Catalog cleans its maildir before each call, but it also can be used as feed collector.

- Poster responds for posting to a site/chat using your email client. Basically each post requires a source to be bind to, but not required if you want to create a new thread for example. Adress Mapper is required by a poster, so when email message to send feeded to ItIsAllMail, it can map email to correct poster to handle.

# Using with Neomutt 

ItIsAllMail highly adopted for usage with Neomutt email client and its limitations. Please see the [tutorial](https://yalexwander.github.io/itsallmail/en/articles/neomutt-tutorial.html).

# Configuration

Many options can be defined on 3 levels:

 - app level, in `conf/config.yml`
 - driver level, in corresponding `lib/ItIsAllMail/Driver/<driver>/driver.cfg`
 - source level, in corresponsing entry of `conf/sources.yml`

Option value defined in source has more priority than driver option, and the driver option has more priority over app option. You can see thi list of options here

So to start you need to add few sources into `conf/sources.yml`, and then start `scripts/monitor.php` from the app root dir(it is important). Then you will get all messages from source converted into emails and put into `mailboxes/default/`. From there you can use any mail client which support maildir format, like:

- neomutt
- mu4e
- thunderbird
- clawsmail
- emacs wanderlust

Or you can serve this maildir using POP3 or IMAP server, for example Dovecot.

## Example screenshots

### A web page with tree discussion:

![Web page](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_website.png)


### Same discussion in representation of maildir, opened in Neomutt:
![Comments as thread](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_discussion.png)
![Article or post](https://raw.githubusercontent.com/yalexwander/itisallmail/master/doc/images/example_message_view.png)


## Installation on Debian/Ubuntu

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
