<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Scripts\Monitor;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Mailbox;

use ItIsAllMail\SendMailProcessor;
use ItIsAllMail\Constants;
use Symfony\Component\Yaml\Yaml;


final class PosterDummyTest extends TestCase {

    public function testSendmailCodes() {
        $appConfig = Yaml::parseFile($GLOBALS["__AppConfigFile"]);

        $processor = new SendMailProcessor($appConfig);


        $sourceManager = new SourceManager($appConfig);
        $source = $sourceManager->getSources()[0];

        $msgRaw = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'post001' . DIRECTORY_SEPARATOR . 'message_uri.eml'
        );
        $msgRaw = str_replace('%%URI_PLACEHOLDER%%', Constants::IAM_HEADER_URI . ": " . $source['url'], $msgRaw);
       

        $exitCode = $processor->process(
            $msgRaw,
            [
                'c' => 'post'
            ]
        );


        $msgRaw .= 'send_must_fail';

        $exitCode = $processor->process(
            $msgRaw,
            [
                'c' => 'post'
            ]
        );

        $this->assertEquals($exitCode, 1, 'Fake sendmail fail exit code');      
    }
}
