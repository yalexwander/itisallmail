<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Scripts\Monitor;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\SourceManager;
use ItIsAllMail\Mailbox;


final class FetcherRssTest extends TestCase {

    public function setUp(): void {
        $testEnvFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "iam-test-data.json";
        $this->testEnv = json_decode( file_get_contents( $testEnvFile ), true );
    }

    public function testFectchCycle(): void {
        $appConfig = yaml_parse_file($GLOBALS["__AppConfigFile"]);

        $monitor = new Monitor($appConfig);

        $timeMap = $monitor->rebuildUpdateTimeMap([]);

        $this->assertEquals(count($timeMap), 1);

        // this must be done someday in separate cycle
        // $monitor->runSourceUpdate(reset($timeMap)["source"]);

        $driverFactory = new FetcherDriverFactory($appConfig);
        $source = reset($timeMap)["source"];
        $source["mailbox_base_dir"] = $this->testEnv['mailbox_base_dir'];

        $driver = $driverFactory->getFetchDriverForSource($source);

        $sourceConfig = new FetcherSourceConfig($appConfig, $driver, $source);

        $mailbox = new Mailbox($sourceConfig);
        $driver->setMailbox($mailbox);

        $posts = $driver->getPosts($source);
        $this->assertEquals(count($posts), 2, 'All posts found in source');

        $mergeResult = $mailbox->mergeMessages($posts);
        $this->assertEquals($mergeResult["added"], 2, 'All posts added to mailbox');

        $outDir = $this->testEnv['mailbox_base_dir'] . DIRECTORY_SEPARATOR . $sourceConfig->getOpt("mailbox") . DIRECTORY_SEPARATOR . "new";
        $newFiles = scandir($outDir);

        $newFileCount = 0;
        foreach ($newFiles as $newFile) {
            if (str_contains($newFile, "@rss")) {
                $newFileCount++;
            }
        }

        $this->assertEquals($mergeResult["added"], 2, 'All posts saved to files');
    }


    // TODO: move this outside test file
    public function tearDown(): void
    {
        system("kill " . $this->testEnv['http_server_pid']);

        rmdir($this->testEnv['mailbox_base_dir']);
    }
}
