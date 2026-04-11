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

    public function testSearchUsers(): void {
        $user = $this->createUser([
            'name' => 'UniqueTokenSearchUser',
            'username' => 'uniquetokensearchuser',
        ]);

        $results = elgg_tokeninput_search_users('UniqueTokenSearch');

        $this->assertIsArray($results);

        $guids = array_map(function ($entity) {
            return $entity->guid;
        }, $results);

        $this->assertContains($user->guid, $guids, 'Search should find the created user');
    }

    public function testSearchGroups(): void {
        $owner = $this->createUser();
        $group = $this->createGroup([
            'name' => 'UniqueTokenSearchGroup',
            'owner_guid' => $owner->guid,
        ]);

        $results = elgg_tokeninput_search_groups('UniqueTokenSearch');

        $this->assertIsArray($results);

        $guids = array_map(function ($entity) {
            return $entity->guid;
        }, $results);

        $this->assertContains($group->guid, $guids, 'Search should find the created group');
    }

    public function testSearchFriendsRequiresLogin(): void {
        _elgg_services()->session_manager->removeLoggedInUser();

        $results = elgg_tokeninput_search_friends('test');

        $this->assertIsArray($results);
        $this->assertEmpty($results, 'Search friends should return empty when not logged in');
    }

    public function testSearchFriendsFindsOnlyFriends(): void {
        $user1 = $this->createUser([
            'name' => 'TokenFriendSearcher',
        ]);
        $friend = $this->createUser([
            'name' => 'TokenFriendTarget',
        ]);
        $nonFriend = $this->createUser([
            'name' => 'TokenFriendStranger',
        ]);

        // Make user1 friends with friend
        $user1->addFriend($friend->guid);

        _elgg_services()->session_manager->setLoggedInUser($user1);

        $results = elgg_tokeninput_search_friends('TokenFriend');

        $guids = array_map(function ($entity) {
            return $entity->guid;
        }, $results);

        $this->assertContains($friend->guid, $guids, 'Should find the friend');
        $this->assertNotContains($nonFriend->guid, $guids, 'Should not find non-friend');
    }

    public function testSearchOwnedEntitiesRequiresLogin(): void {
        _elgg_services()->session_manager->removeLoggedInUser();

        $results = elgg_tokeninput_search_owned_entities('test');

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

        $results = elgg_tokeninput_search_tags('uniquetokentag');

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
        $secret = elgg_tokeninput_get_secret();

        $this->assertIsString($secret);
        $this->assertNotEmpty($secret, 'Secret should be a non-empty string');

        // Call again - should return the same persisted value
        $secret2 = elgg_tokeninput_get_secret();
        $this->assertEquals($secret, $secret2, 'Secret should be persisted across calls');
    }
}
