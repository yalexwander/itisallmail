<?php

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;

interface AddressMapperInterface
{

    /**
      Given some thread id it tries to return the id/url of the source, which
      can be directly mapped to one of the sources from sources.yml Returning
      null means mapper is not suitable

      $msg - is an array with parsed email message
     */
    public function mapMessageToSource(ParsedMessage $msg): ?Source;

    public function canMapThis(ParsedMessage $msg, string $mapType = null): ?bool;
}
