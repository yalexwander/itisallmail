<?php

namespace ItIsAllMail\Interfaces;

interface AddressMapperInterface
{

    /**
      Given some thread id it tries to return the id/url of the source, which
      can be directly mapped to one of the sources from sources.yml Returning
      null means mapper is not suitable

      $msg - is an array with parsed email message
     */
    public function mapMessageToSource(array $msg): ?array;

    public function canMapThis(array $msg, string $mapType = null): ?bool;
}
