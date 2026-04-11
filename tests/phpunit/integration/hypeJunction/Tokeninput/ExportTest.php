<?php

namespace hypeJunction\Tokeninput;

use Elgg\IntegrationTestCase;

/**
 * Integration tests for entity/metadata export functions.
 */
class ExportTest extends IntegrationTestCase {

    private static bool $pluginBooted = false;

    public function up() {
        if (!self::$pluginBooted) {
            $pluginDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
            require_once $pluginDir . '/lib/tokeninput.php';
            self::$pluginBooted = true;
        }
    }

    public function down() {}

    public function testExportUser(): void {
        $user = $this->createUser([
            'name' => 'Token Test User',
            'username' => 'tokentestuser',
        ]);

        $export = elgg_tokeninput_export_entity($user);

        $this->assertIsArray($export);
        $this->assertStringContainsString('Token Test User', $export['label']);
        $this->assertStringContainsString('tokentestuser', $export['label']);
        $this->assertEquals($user->guid, $export['value']);
        $this->assertEquals('user', $export['type']);
    }

    public function testExportGroup(): void {
        $user = $this->createUser();
        $group = $this->createGroup([
            'name' => 'Token Test Group',
            'owner_guid' => $user->guid,
        ]);

        $export = elgg_tokeninput_export_entity($group);

        $this->assertIsArray($export);
        $this->assertEquals('Token Test Group', $export['label']);
        $this->assertEquals($group->guid, $export['value']);
        $this->assertEquals('group', $export['type']);
    }

    public function testExportObject(): void {
        $user = $this->createUser();
        $object = $this->createObject([
            'subtype' => 'blog',
            'title' => 'Token Test Object',
            'owner_guid' => $user->guid,
        ]);

        $export = elgg_tokeninput_export_entity($object);

        $this->assertIsArray($export);
        $this->assertEquals($object->guid, $export['value']);
        $this->assertEquals('object', $export['type']);
        $this->assertEquals('blog', $export['subtype']);
    }

    public function testExportEntityHasRequiredKeys(): void {
        $user = $this->createUser();

        $export = elgg_tokeninput_export_entity($user);

        $requiredKeys = ['label', 'value', 'metadata', 'icon', 'type', 'subtype', 'html_result', 'html_token'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $export, "Export should contain key: {$key}");
        }
    }

    public function testExportMetadataWithElggMetadata(): void {
        $user = $this->createUser();
        $object = $this->createObject([
            'subtype' => 'blog',
            'owner_guid' => $user->guid,
        ]);

        $object->tags = 'testtag';

        $metadataArray = elgg_get_metadata([
            'guid' => $object->guid,
            'metadata_names' => ['tags'],
            'limit' => 1,
        ]);

        $this->assertNotEmpty($metadataArray, 'Metadata should exist');

        $metadata = $metadataArray[0];
        $export = elgg_tokeninput_export_metadata($metadata);

        $this->assertIsArray($export);
        $this->assertEquals('testtag', $export['label']);
        $this->assertEquals('testtag', $export['value']);
        $this->assertEquals('tags', $export['type']);
    }

    public function testExportMetadataWithString(): void {
        $export = elgg_tokeninput_export_metadata('sometag');

        $this->assertIsArray($export);
        $this->assertEquals('sometag', $export['label']);
        $this->assertEquals('sometag', $export['value']);
        $this->assertEquals('tag', $export['type']);
    }
}
