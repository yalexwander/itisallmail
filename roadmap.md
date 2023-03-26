- Support for putting all sent messages to separate folder named my-threads
  
  Copy all messages from all sources to this folder in case it has any message as ancestor from this folder

- support for "limit_to_subtreads" option with array type of value

  Save only messages that have one of message ids listed in limit_to_subtreads as ancestor
  
- error folder and error processing

  create a message with error in corresponding folder to 

- maildir serializator

  create a single html page, or set of pages(if there are too many messages), friendly for SEO and viewing in browser

- attachement view feature.
  
  Idea is to make possible viewing attachements from posts in neomutt by single button press. For example on pager view you press ",v" and there is a popup menu which propose to chose which attachement must be downloaded/opened. This feature must be supported by driver as a special subdriver like Fetcher or Poster.
