<?php

namespace ItIsAllMail\Action;

use CatalogActionHandler;

/**
 * At this point it must work completely as Catalog, with difference that
 * Catalog must clear mailbox before each request. Feed must support the same
 * query syntax, but only add messages.
 */
class FeedActionHandler extends CatalogActionHandler {
}
