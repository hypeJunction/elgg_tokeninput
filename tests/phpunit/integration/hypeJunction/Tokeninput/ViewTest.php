<?php

namespace hypeJunction\Tokeninput;

use Elgg\IntegrationTestCase;

/**
 * Integration tests for tokeninput input views.
 */
class ViewTest extends IntegrationTestCase {

    private static bool $pluginBooted = false;

    public function up() {
        if (!self::$pluginBooted) {
            $pluginDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
            require_once $pluginDir . '/lib/tokeninput.php';
            self::$pluginBooted = true;
        }
    }

    public function down() {}

    public function testTokeninputViewRenders(): void {
        $output = elgg_view('input/tokeninput', [
            'name' => 'test',
        ]);

        $this->assertNotEmpty($output, 'Tokeninput view should render non-empty HTML');
        $this->assertIsString($output);
    }

    public function testUserpickerViewRenders(): void {
        $output = elgg_view('input/userpicker', [
            'name' => 'test',
        ]);

        $this->assertNotEmpty($output, 'Userpicker view should render non-empty HTML');
        $this->assertIsString($output);
    }

    public function testTagsViewRenders(): void {
        $output = elgg_view('input/tokeninput', [
            'name' => 'tags',
            'callback' => 'elgg_tokeninput_search_tags',
        ]);

        $this->assertNotEmpty($output, 'Tags input view should render non-empty HTML');
        $this->assertIsString($output);
    }
}
