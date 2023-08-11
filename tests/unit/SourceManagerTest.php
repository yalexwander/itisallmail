<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ItIsAllMail\SourceManager;
use ItIsAllMail\CoreTypes\Source;

final class SourceManagerTest extends TestCase {

    protected SourceManager $sourcemanager;

    protected function setUp(): void
    {
        $this->sourceManager = new SourceManager([], [
            $GLOBALS['__AppConfigDir'] . DIRECTORY_SEPARATOR . "sources.yml"
        ]);
    }
    
    public function testGetSourceById(): void {
        $this->assertInstanceOf(Source::class, $this->sourceManager->getSourceById('test_rss_url'));
        $this->assertEquals(null, $this->sourceManager->getSourceById('none'));
    }
}
