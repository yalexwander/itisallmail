<?php

namespace ItIsAllMail\CoreTypes\Exception;

// Throw it in situation when request to service was done, data was received, but it was not that data your parser expects on regular basis

class UnexpectedResponseDataException extends \Exception {
}
