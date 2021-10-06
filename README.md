# ItIsAllMail

A tool to parse forums, imageboards, all sites with discussions to a
mailbox format. Tools for working with mail are much more powerful and
reluctant, than any site interface.

## Example

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
