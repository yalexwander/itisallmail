# Fetcher

`scripts/fetcher.php` is a script responding on fetching all sources,
and putting the generated emails to mailboxes. Depending on how your
mail client works with maildir, all new messages will be just added,
while old ones left untouched.

In worst case you will have copies of the same message after fetcher
being run. Please create an issue.
