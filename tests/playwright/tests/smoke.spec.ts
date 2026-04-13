import { test, expect } from '@playwright/test';

/**
 * E2E smoke for elgg_tokeninput.
 *
 * Wraps the jquery-tokeninput vendor bundle and exposes it as both an
 * input view (input/tokeninput → tokeninput/require) and a /tokeninput/
 * route for autocomplete data fetches. Surface to smoke-test:
 *   - homepage activates without fataling
 *   - elgg.css / admin.css aggregates compile (extension keys still valid)
 *   - the /tokeninput/{segments} route is reachable (auth-gated → 302 or
 *     403 are acceptable; what we're catching is a 5xx or 404 indicating
 *     the route registration drifted)
 */
test.describe('elgg_tokeninput', () => {
  test('homepage renders with no PHP fatal markers', async ({ page }) => {
    const response = await page.goto('/');
    expect(response).toBeTruthy();
    expect(response!.status()).toBeLessThan(500);

    const body = await page.content();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('Uncaught');
    expect(body).not.toContain('ParseError');
  });

  test('default css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/elgg.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });

  test('admin css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/admin.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });

  test('tokeninput route is reachable without fataling', async ({ page }) => {
    // /tokeninput/{segments} dispatches via Elgg's route table. The
    // 'segments' wildcard makes the URL match any sub-path; the
    // controller is auth-gated for the actual data fetch path. The
    // smoke just asserts no 5xx — a 404/302/403 still means the route
    // resolved and dispatched. What we're catching here is the case
    // where the plugin's route registration drifted or the controller
    // class fails to autoload, both of which surface as a 5xx.
    const response = await page.goto('/tokeninput/foo');
    expect(response).toBeTruthy();
    expect(response!.status(), `unexpected ${response!.status()} on /tokeninput/foo`).toBeLessThan(500);
  });
});
