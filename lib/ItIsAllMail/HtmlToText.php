<?php

namespace ItIsAllMail;

use Html2Text\Html2Text;

class HtmlToText extends Html2Text
{
    protected $options = [
        "do_links" => "inline",
        "width"    => 140,
    ];
}
