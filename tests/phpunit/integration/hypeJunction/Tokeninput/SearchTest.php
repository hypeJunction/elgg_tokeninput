<?php

namespace hypeJunction\Tokeninput;

use Elgg\IntegrationTestCase;

/**
 * Integration tests for tokeninput search functions.
 */
class SearchTest extends IntegrationTestCase {

    private static bool $pluginBooted = false;

    public function up() {
        if (!self::$pluginBooted) {
            $pluginDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
            require_once $pluginDir . '/lib/tokeninput.php';
            self::$pluginBooted = true;
        }
    }

    public function down() {}

    public function testSearchUsersReturnsArray(): void {
        $results = \elgg_tokeninput_search_users('test');

        $this->assertIsArray($results);
    }

    public function testSearchGroupsReturnsArray(): void {
        $results = \elgg_tokeninput_search_groups('test');

        $this->assertIsArray($results);
    }

    public function testSearchFriendsReturnsArrayWhenNotLoggedIn(): void {
        $results = \elgg_tokeninput_search_friends('test');

        $this->assertIsArray($results);
        $this->assertEmpty($results, 'Search friends should return empty when not logged in');
    }

    public function testSearchFriendsReturnsArray(): void {
        $user1 = $this->createUser();

        \elgg_get_session()->setLoggedInUser($user1);

        $results = \elgg_tokeninput_search_friends('test');

        $this->assertIsArray($results);
    }

    public function testSearchOwnedEntitiesReturnsArrayWhenNotLoggedIn(): void {
        $results = \elgg_tokeninput_search_owned_entities('test');

        $this->assertIsArray($results);
        $this->assertEmpty($results, 'Search owned entities should return empty when not logged in');
    }

    public function testSearchTags(): void {
        $user = $this->createUser();
        $object = $this->createObject([
            'subtype' => 'blog',
            'owner_guid' => $user->guid,
        ]);

        $object->tags = 'uniquetokentagvalue';

        $results = \elgg_tokeninput_search_tags('uniquetokentag');

        $this->assertNotEmpty($results, 'Tag search should return results');

        $found = false;
        foreach ($results as $metadata) {
            if ($metadata->value === 'uniquetokentagvalue') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Should find the tag metadata');
    }

    public function testGetSecret(): void {
        $secret = \elgg_tokeninput_get_secret();

        $this->assertIsString($secret);
        $this->assertNotEmpty($secret, 'Secret should be a non-empty string');

        // Call again - should return the same persisted value
        $secret2 = \elgg_tokeninput_get_secret();
        $this->assertEquals($secret, $secret2, 'Secret should be persisted across calls');
    }
}
